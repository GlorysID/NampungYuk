<?php

namespace Tests\Feature;

use App\Services\MediaOptimizer;
use Tests\TestCase;

class MediaOptimizerTest extends TestCase
{
    private function makeJpeg(int $w, int $h): string
    {
        $im = imagecreatetruecolor($w, $h);
        for ($i = 0; $i < 500; $i++) {
            $c = imagecolorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255));
            imagefilledellipse($im, rand(0, $w), rand(0, $h), rand(10, 80), rand(10, 80), $c);
        }
        $path = tempnam(sys_get_temp_dir(), 'ny_test_').'.jpg';
        imagejpeg($im, $path, 92);
        imagedestroy($im);

        return $path;
    }

    public function test_image_is_converted_to_webp_and_downscaled(): void
    {
        $opt = new MediaOptimizer();
        $src = $this->makeJpeg(2400, 1600);

        $bytes = $opt->encodeWebp($src, $opt->galleryMaxWidth);

        // Output must be a valid WebP.
        $info = getimagesizefromstring($bytes);
        $this->assertNotFalse($info);
        $this->assertEquals('image/webp', $info['mime']);
        // Width is capped, aspect ratio preserved (2400x1600 → 1600x1067).
        $this->assertLessThanOrEqual($opt->galleryMaxWidth, $info[0]);
        $this->assertEqualsWithDelta(2400 / 1600, $info[0] / $info[1], 0.05);

        @unlink($src);
    }

    public function test_avatar_uses_smaller_cap(): void
    {
        $opt = new MediaOptimizer();
        $src = $this->makeJpeg(1000, 1000);

        $bytes = $opt->encodeWebp($src, $opt->avatarMaxWidth);
        $info = getimagesizefromstring($bytes);

        $this->assertLessThanOrEqual(256, $info[0]);

        @unlink($src);
    }

    public function test_smaller_image_is_not_upscaled(): void
    {
        $opt = new MediaOptimizer();
        $src = $this->makeJpeg(400, 300);

        $bytes = $opt->encodeWebp($src, $opt->galleryMaxWidth);
        $info = getimagesizefromstring($bytes);

        $this->assertEquals(400, $info[0]);

        @unlink($src);
    }
}
