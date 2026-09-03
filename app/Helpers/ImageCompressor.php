<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageCompressor
{
    /**
     * Compress an uploaded image file (max width & JPEG quality) and store it in specified disk/folder.
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string $disk
     * @param int $maxWidth
     * @param int $quality
     * @return string Relative storage path of compressed image
     */
    public static function compressAndStore(UploadedFile $file, string $folder = 'compressed_images', string $disk = 'public', int $maxWidth = 1200, int $quality = 80): string
    {
        $realPath = $file->getRealPath();

        // If file doesn't exist or is invalid, fallback to default store
        if (!$file->isValid() || empty($realPath) || !file_exists($realPath)) {
            return $file->store($folder, $disk);
        }

        // Try reading image with GD
        $imageData = @file_get_contents($realPath);
        if (!$imageData) {
            return $file->store($folder, $disk);
        }

        $srcImage = @imagecreatefromstring($imageData);
        if (!$srcImage) {
            return $file->store($folder, $disk);
        }

        $width = imagesx($srcImage);
        $height = imagesy($srcImage);

        // Calculate resize dimensions while maintaining aspect ratio
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) round(($height / $width) * $newWidth);
            $dstImage = imagescale($srcImage, $newWidth, $newHeight);
            imagedestroy($srcImage);
        } else {
            $dstImage = $srcImage;
        }

        // Generate filename
        $filename = Str::uuid()->toString() . '.jpg';
        $relativeFolder = trim($folder, '/');
        $relativePath = $relativeFolder . '/' . $filename;

        // Output JPEG into memory buffer
        ob_start();
        imagejpeg($dstImage, null, $quality);
        $compressedData = ob_get_clean();

        imagedestroy($dstImage);

        // Store to specified disk
        Storage::disk($disk)->put($relativePath, $compressedData);

        return $relativePath;
    }
}
