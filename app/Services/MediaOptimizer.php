<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Media optimizer — shrinks uploaded images and videos before they hit
 * storage, so pages load light.
 *
 *  - Images: re-encoded to WebP (transparency preserved) and downscaled.
 *  - Videos: re-encoded to H.264 (CRF) with faststart + a poster frame.
 *
 * GD and ffmpeg are used directly — no external Composer packages required.
 */
class MediaOptimizer
{
    /** Max width (px) for a project thumbnail / banner-like asset. */
    public int $thumbnailMaxWidth = 1280;

    /** Max width (px) for avatars (displayed small). */
    public int $avatarMaxWidth = 256;

    /** Max width (px) for gallery images. */
    public int $galleryMaxWidth = 1600;

    /** Max width (px) for a compressed video (downscale larger sources). */
    public int $videoMaxWidth = 1920;

    /** H.264 quality: lower = better quality, larger file. */
    public int $videoCrf = 28;

    /** WebP encode quality (0-100). */
    public int $webpQuality = 80;

    /**
     * Optimize an uploaded image → returns the stored relative path on the
     * given disk (without the /storage prefix). Falls back to a plain store
     * if GD can't process the file.
     *
     * @param  'avatar'|'thumb'|'gallery'  $kind
     */
    public function storeImage(UploadedFile $file, string $folder, string $kind = 'gallery', string $disk = 'public'): string
    {
        $maxWidth = match ($kind) {
            'avatar' => $this->avatarMaxWidth,
            'thumb' => $this->thumbnailMaxWidth,
            default => $this->galleryMaxWidth,
        };

        try {
            $webp = $this->encodeWebp($file->getRealPath(), $maxWidth);
        } catch (Throwable $e) {
            // Any GD failure → just store the original so uploads never break.
            return $file->store($folder, $disk);
        }

        $name = $folder.'/'.Str::random(40).'.webp';
        Storage::disk($disk)->put($name, $webp);

        return $name;
    }

    /**
     * Optimize an uploaded video → returns ['path' => relative, 'poster' => relative|null].
     * Falls back to a plain store when ffmpeg is unavailable or fails.
     *
     * @return array{path: string, poster: ?string}
     */
    public function storeVideo(UploadedFile $file, string $folder, string $disk = 'public'): array
    {
        $src = $file->getRealPath();
        $tmpOut = tempnam(sys_get_temp_dir(), 'ny_vid_').'.mp4';

        $ffmpeg = $this->ffmpegBinary();
        if (! $ffmpeg) {
            return ['path' => $file->store($folder, $disk), 'poster' => null];
        }

        // Re-encode: H.264 + AAC, capped width (keeps aspect), +faststart for web.
        $scale = "scale='min({$this->videoMaxWidth},iw)':-2";
        $cmd = sprintf(
            '%s -y -i %s -vf %s -c:v libx264 -crf %d -preset veryfast -c:a aac -b:a 128k -movflags +faststart %s 2>&1',
            escapeshellcmd($ffmpeg),
            escapeshellarg($src),
            escapeshellarg($scale),
            $this->videoCrf,
            escapeshellarg($tmpOut)
        );

        exec($cmd, $out, $code);

        if ($code !== 0 || ! file_exists($tmpOut) || filesize($tmpOut) === 0) {
            @unlink($tmpOut);

            return ['path' => $file->store($folder, $disk), 'poster' => null];
        }

        $name = $folder.'/'.Str::random(40).'.mp4';
        Storage::disk($disk)->put($name, file_get_contents($tmpOut));
        @unlink($tmpOut);

        // Poster frame (1 second in, or 0 if the clip is shorter).
        $poster = $this->extractPoster($src, $folder, $disk);

        return ['path' => $name, 'poster' => $poster];
    }

    /**
     * Extract a single frame as a WebP poster.
     */
    public function extractPoster(string $videoPath, string $folder, string $disk = 'public'): ?string
    {
        $ffmpeg = $this->ffmpegBinary();
        if (! $ffmpeg) {
            return null;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'ny_poster_').'.jpg';
        $cmd = sprintf(
            '%s -y -ss 1 -i %s -frames:v 1 -vf %s %s 2>&1',
            escapeshellcmd($ffmpeg),
            escapeshellarg($videoPath),
            escapeshellarg('scale=1280:-2'),
            escapeshellarg($tmp)
        );

        exec($cmd, $out, $code);

        if ($code !== 0 || ! file_exists($tmp) || filesize($tmp) === 0) {
            @unlink($tmp);

            return null;
        }

        try {
            $webp = $this->encodeWebp($tmp, 1280);
        } catch (Throwable $e) {
            @unlink($tmp);

            return null;
        }
        @unlink($tmp);

        $name = $folder.'/'.Str::random(40).'_poster.webp';
        Storage::disk($disk)->put($name, $webp);

        return $name;
    }

    /**
     * Resize + re-encode a source image file into WebP bytes.
     *
     * @throws RuntimeException when the source can't be decoded.
     */
    public function encodeWebp(string $sourcePath, int $maxWidth): string
    {
        $info = @getimagesize($sourcePath);
        if (! $info) {
            throw new RuntimeException('Unreadable image: '.$sourcePath);
        }

        [$width, $height] = $info;
        $type = $info[2];

        $src = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_GIF => @imagecreatefromgif($sourcePath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($sourcePath),
            default => null,
        };

        if (! $src) {
            throw new RuntimeException('Unsupported image type.');
        }

        // Downscale only when needed (never upscale).
        $newWidth = min($width, $maxWidth);
        $newHeight = (int) round($height * ($newWidth / max($width, 1)));

        $dst = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG/GIF/WebP sources.
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        imagewebp($dst, null, $this->webpQuality);
        $bytes = (string) ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        if ($bytes === '') {
            throw new RuntimeException('Failed to encode WebP.');
        }

        return $bytes;
    }

    /**
     * Resolve the ffmpeg binary (config override → PATH → common locations).
     * Result is cached for the process.
     */
    protected function ffmpegBinary(): ?string
    {
        static $resolved = false;
        static $binary = null;

        if ($resolved) {
            return $binary;
        }
        $resolved = true;

        // 1. Explicit config / env path.
        $configured = config('services.ffmpeg.path');
        if ($configured && is_file($configured)) {
            return $binary = $configured;
        }

        // 2. On PATH.
        if ($this->binaryWorks('ffmpeg')) {
            return $binary = 'ffmpeg';
        }

        // 3. Common Windows install locations (WinGet / Chocolatey / scoop).
        $candidates = [
            getenv('LOCALAPPDATA').'\\Microsoft\\WinGet\\Packages',
            'C:\\ProgramData\\chocolatey\\bin\\ffmpeg.exe',
            getenv('USERPROFILE').'\\scoop\\shims\\ffmpeg.exe',
            '/usr/bin/ffmpeg',
            '/usr/local/bin/ffmpeg',
        ];

        foreach ($candidates as $candidate) {
            if (! $candidate) {
                continue;
            }
            if (is_file($candidate) && $this->binaryWorks($candidate)) {
                return $binary = $candidate;
            }
            // WinGet nests the binary under a hashed folder — glob for it.
            if (is_dir($candidate)) {
                foreach (glob($candidate.'\\*\\*\\bin\\ffmpeg.exe') ?: [] as $found) {
                    if ($this->binaryWorks($found)) {
                        return $binary = $found;
                    }
                }
            }
        }

        return $binary = null;
    }

    /**
     * Whether a candidate ffmpeg executable actually runs.
     */
    protected function binaryWorks(string $path): bool
    {
        exec(escapeshellarg($path).' -version 2>&1', $out, $code);

        return $code === 0;
    }
}
