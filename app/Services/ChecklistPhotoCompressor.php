<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ChecklistPhotoCompressor
{
    private const MAX_SIZE_BYTES = 1024 * 1024 - 1;

    private const MIN_QUALITY = 30;

    private const QUALITY_STEP = 10;

    private const MIN_DIMENSION = 640;

    public function store(UploadedFile $file): string
    {
        $source = $this->createImage($file);
        $source = $this->orientImage($source, $file->getRealPath());
        $width = imagesx($source);
        $height = imagesy($source);

        try {
            for ($quality = 85; $quality >= self::MIN_QUALITY; $quality -= self::QUALITY_STEP) {
                $encoded = $this->encode($source, $quality);

                if (strlen($encoded) <= self::MAX_SIZE_BYTES) {
                    return $this->storeEncoded($encoded);
                }
            }

            while (max($width, $height) > self::MIN_DIMENSION) {
                $scale = min(0.8, self::MIN_DIMENSION / max($width, $height));
                $width = max(1, (int) floor($width * $scale));
                $height = max(1, (int) floor($height * $scale));
                $resized = imagescale($source, $width, $height, IMG_BICUBIC_FIXED);
                imagedestroy($source);
                $source = $resized;

                for ($quality = 85; $quality >= self::MIN_QUALITY; $quality -= self::QUALITY_STEP) {
                    $encoded = $this->encode($source, $quality);

                    if (strlen($encoded) <= self::MAX_SIZE_BYTES) {
                        return $this->storeEncoded($encoded);
                    }
                }
            }
        } finally {
            imagedestroy($source);
        }

        throw new RuntimeException('Foto tidak dapat dikompres hingga di bawah 1 MB.');
    }

    private function createImage(UploadedFile $file): \GdImage
    {
        return match ($file->getMimeType()) {
            'image/jpeg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png' => $this->createPngImage($file->getRealPath()),
            default => throw new RuntimeException('Format foto tidak didukung.'),
        };
    }

    private function createPngImage(string $path): \GdImage
    {
        $png = imagecreatefrompng($path);
        $background = imagecreatetruecolor(imagesx($png), imagesy($png));
        imagefill($background, 0, 0, imagecolorallocate($background, 255, 255, 255));
        imagecopy($background, $png, 0, 0, 0, 0, imagesx($png), imagesy($png));
        imagedestroy($png);

        return $background;
    }

    private function orientImage(\GdImage $image, string $path): \GdImage
    {
        if (! function_exists('exif_read_data') || ! in_array('image/jpeg', [mime_content_type($path)], true)) {
            return $image;
        }

        $orientation = exif_read_data($path)['Orientation'] ?? null;
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
        imagedestroy($image);

        return $rotated;
    }

    private function encode(\GdImage $image, int $quality): string
    {
        ob_start();
        imagejpeg($image, null, $quality);

        return (string) ob_get_clean();
    }

    private function storeEncoded(string $encoded): string
    {
        $path = 'checklist-photos/'.uniqid('photo_', true).'.jpg';
        Storage::disk('public')->put($path, $encoded);

        return $path;
    }
}
