<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class DeleteImage
{
    public function deleteImage($path)
    {
        Storage::disk('public')->delete($path);

        return true;
    }
}
