<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // them moi

class ImageUploadService
{
    /**
     * Upload image and save to database
     *
     * @param  Model  $model  (Product or Sku)
     */
    public static function upload(UploadedFile $image, $model): ?Image
    {
        try {
            $filename = Str::uuid().'.'.$image->getClientOriginalExtension();
            $path = 'products/'.class_basename($model)."/{$model->id}/{$filename}";

            // Kiểm tra và đảm bảo tệp hợp lệ trước khi upload
            if (! $image->isValid()) {
                throw new ApiException('File upload không hợp lệ.');
            }

            // Upload lên S3
            Storage::disk('s3')->put($path, file_get_contents($image), 'public');
            // Storage::disk('s3')->put($path, fopen($image->getPathname(), 'r+'), 'public');

            // Lưu vào DB
            return $model->images()->create([
                'image_url' => Storage::url($path),
            ]);
        } catch (\Exception $e) {
            // \Log::error('Upload failed: ' . $e->getMessage());
            // Ném ngoại lệ có thể xử lý được thay vì trả về null
            throw new ApiException('Lỗi trong quá trình upload hình ảnh.', 500);
        }
    }
}
