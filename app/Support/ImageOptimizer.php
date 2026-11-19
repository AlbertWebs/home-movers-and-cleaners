<?php

namespace App\Support;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    public function store(UploadedFile $upload, string $directory): string
    {
        $contents = file_get_contents($upload->getRealPath());
        $source = $contents === false ? false : @imagecreatefromstring($contents);

        if (! $source || ! function_exists('imagewebp')) {
            if ($source) {
                imagedestroy($source);
            }

            return $upload->store($directory, 'public');
        }

        $source = $this->applyOrientation($source, $upload);
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = min(1, 2400 / max($sourceWidth, $sourceHeight));
        $width = max(1, (int) round($sourceWidth * $scale));
        $height = max(1, (int) round($sourceHeight * $scale));
        $optimized = imagecreatetruecolor($width, $height);
        imagealphablending($optimized, false);
        imagesavealpha($optimized, true);
        imagecopyresampled($optimized, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        $temporaryPath = tempnam(sys_get_temp_dir(), 'hmc-image-');
        if ($temporaryPath === false) {
            imagedestroy($source);
            imagedestroy($optimized);
            throw new \RuntimeException('An optimized image could not be prepared.');
        }

        try {
            if (! imagewebp($optimized, $temporaryPath, 82)) {
                throw new \RuntimeException('An optimized image could not be encoded.');
            }

            $storedPath = Storage::disk('public')->putFileAs($directory, new File($temporaryPath), Str::uuid().'.webp');
            if ($storedPath === false) {
                throw new \RuntimeException('An optimized image could not be stored.');
            }

            return $storedPath;
        } finally {
            imagedestroy($source);
            imagedestroy($optimized);
            @unlink($temporaryPath);
        }
    }

    private function applyOrientation(\GdImage $image, UploadedFile $upload): \GdImage
    {
        if (! in_array(strtolower($upload->getClientOriginalExtension()), ['jpg', 'jpeg'], true)
            || ! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = @exif_read_data($upload->getRealPath())['Orientation'] ?? 1;
        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };
        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);
        if ($rotated instanceof \GdImage) {
            imagedestroy($image);

            return $rotated;
        }

        return $image;
    }
}
