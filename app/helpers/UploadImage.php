<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class UploadImage
{
    public function uploadImage($path, $image)
    {
        // Generate a unique filename with timestamp and original extension
        $filename = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();

        // Store the image in the gallery directory within the public disk
        $path = $image->storeAs($path, $filename, 'public');

        // Return the URL path that can be used to access the image

        $image = [
            'filename' => $filename,
            'path' => url(Storage::url($path)),
        ];

        return $image;
    }
}
