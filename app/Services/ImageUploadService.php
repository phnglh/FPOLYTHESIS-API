<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\Models\Image;

class ImageUploadService
{
    /**
     * Upload image and save to database
     *
     * @param UploadedFile $image
     * @param Model $model (Product or Sku)
     * @return Image|null
     */
    public static function upload(UploadedFile $image, $model): ?Image
    {
        try {
            $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
            $path = "products/" . class_basename($model) . "/{$model->id}/{$filename}";

            // Upload lên S3
            Storage::disk('s3')->put($path, file_get_contents($image), 'public');
            // Storage::disk('s3')->put($path, fopen($image->getPathname(), 'r+'), 'public');

            // Lưu vào DB
            return $model->images()->create([
                'image_url' => Storage::url($path),
            ]);
        } catch (\Exception $e) {
            // \Log::error('Upload failed: ' . $e->getMessage());
            return null;
        }
    }
}
