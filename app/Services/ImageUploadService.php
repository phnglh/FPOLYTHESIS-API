<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageUploadService
{
    public function uploadSingle(UploadedFile $file, bool $isPublic = true, $model = null)
    {
        if ($file->isValid()) {
            $extension = $file->getClientOriginalExtension();
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $fileName = $originalName . '-' . uniqid() . '.' . $extension;
            $path = Storage::disk('s3')->putFileAs('', $file, $fileName, $isPublic ? 'public' : 'private');

            return Storage::disk('s3')->url($fileName);
        }
        return null;
    }

    public function uploadMultiple(array $files, bool $isPublic = true, $model = null)
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $fileName = $originalName . '-' . uniqid() . '.' . $extension;

            // Upload lên S3
            $path = Storage::disk('s3')->putFileAs('', $file, $fileName, $isPublic ? 'public' : 'private');
            $uploadedFiles[] = Storage::disk('s3')->url($fileName);

        }

        return $uploadedFiles;
    }

}
