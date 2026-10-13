---
paths:
  - app/Services/MediaOptimizer.php
---

# Services

## Media uploads are optimized via MediaOptimizer
All image uploads go through `App\Services\MediaOptimizer::storeImage($file, $folder, $kind)` where $kind is 'avatar' (max 256px), 'thumb' (1280px), or 'gallery' (1600px) — output is always WebP. Video uploads use `storeVideo()` → H.264 CRF 28, downscaled to 1920px, +faststart, and returns ['path','poster'] (poster is an auto-extracted WebP frame stored in the projects.video_poster column). ffmpeg is located via config('services.ffmpeg.path') / FFMPEG_PATH env → PATH → common locations. Both fall back to a plain store if GD/ffmpeg fails, so uploads never hard-fail. Wired into ProjectController@store, ProfileController@update, CommunityPostController@store.
