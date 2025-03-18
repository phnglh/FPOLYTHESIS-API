<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ImageUploadService
{
    /**
     * Upload một hoặc nhiều hình ảnh lên S3.
     *
     * @param UploadedFile|array $images
     * @param mixed $model
     * @return array|null
     */
    public static function upload($images, $model)
    {
        Log::info('Images received:', ['images' => $images]);
        $uploadedUrls = [];

        if (is_array($images)) {
            foreach ($images as $image) {
                if ($image instanceof UploadedFile) {
                    $path = $image->store("uploads/{$model->getTable()}/{$model->id}", 's3');
                    Storage::disk('s3')->setVisibility($path, 'public');
                    $uploadedUrls[] = Storage::disk('s3')->url($path);
                }
            }
        } elseif ($images instanceof UploadedFile) {
            $path = $images->store("uploads/{$model->getTable()}/{$model->id}", 's3');
            Storage::disk('s3')->setVisibility($path, 'public');
            $uploadedUrls[] = Storage::disk('s3')->url($path);
        }

        Log::info('Uploaded URLs:', $uploadedUrls);
        return $uploadedUrls;
    }
}
