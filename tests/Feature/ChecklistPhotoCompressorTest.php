<?php

namespace Tests\Feature;

use App\Services\ChecklistPhotoCompressor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChecklistPhotoCompressorTest extends TestCase
{
    public function test_it_stores_uploaded_photo_below_one_megabyte(): void
    {
        Storage::fake('public');
        $sourcePath = tempnam(sys_get_temp_dir(), 'checklist-photo-');
        $image = imagecreatetruecolor(2400, 1800);

        for ($y = 0; $y < 1800; $y++) {
            for ($x = 0; $x < 2400; $x++) {
                imagesetpixel($image, $x, $y, imagecolorallocate($image, ($x * 13) % 256, ($y * 17) % 256, ($x + $y) % 256));
            }
        }

        imagejpeg($image, $sourcePath, 100);
        imagedestroy($image);

        try {
            $file = new UploadedFile($sourcePath, 'photo.jpg', 'image/jpeg', null, true);
            $path = app(ChecklistPhotoCompressor::class)->store($file);

            Storage::disk('public')->assertExists($path);
            $this->assertLessThan(1024 * 1024, strlen(Storage::disk('public')->get($path)));
            $this->assertSame('image/jpeg', Storage::disk('public')->mimeType($path));
        } finally {
            @unlink($sourcePath);
        }
    }
}
