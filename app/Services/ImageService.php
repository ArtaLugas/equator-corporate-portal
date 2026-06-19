<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageService
{
    /**
     * Raster formats we downscale + recompress. Anything else (SVG, ICO,
     * animated GIF, …) is stored untouched so it is never corrupted.
     */
    private const OPTIMIZABLE = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Store an uploaded image on the public disk, downscaling oversized raster
     * images and recompressing them to keep payloads light (Core Web Vitals).
     *
     * Returns the stored relative path (e.g. "hero-banners/169...-ab12cd.jpg").
     * Falls back to storing the original untouched if optimization fails, so an
     * upload is never lost to an encoding edge case.
     *
     * @param  string|null  $name  Human label folded into the filename for readability.
     * @param  int  $maxWidth  Hard cap on width; taller/narrower aspect ratios are preserved.
     */
    public function store(UploadedFile $image, string $folder, ?string $name = null, int $maxWidth = 2400): string
    {
        $ext = strtolower($image->getClientOriginalExtension() ?: 'jpg');
        $slug = $name ? '-'.Str::slug($name) : '';
        $path = trim($folder, '/').'/'.time().'-'.Str::random(6).$slug.'.'.$ext;

        if (in_array($ext, self::OPTIMIZABLE, true)) {
            try {
                $img = (new ImageManager(new Driver))->read($image->getRealPath());

                if ($img->width() > $maxWidth) {
                    $img->scaleDown(width: $maxWidth);
                }

                Storage::disk('public')->put($path, (string) $img->encodeByExtension($ext, quality: 80));

                return $path;
            } catch (\Throwable $e) {
                // Optimization is best-effort — never block an upload over it.
                report($e);
            }
        }

        return $image->storeAs(dirname($path), basename($path), 'public');
    }
}
