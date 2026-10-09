<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\FileHelper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class UploadImageEditor extends Controller
{
    # image handleler
    public function uploadImage(Request $request)
    {
        $file = $request->file('file');
        if (! $file || ! $file->isValid()) {
            $limit = ini_get('upload_max_filesize');
            return response()->json(['error' => "Gambar gagal diterima server. Ukuran maksimal saat ini {$limit}."], 422);
        }

        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) || ! @getimagesize($file->getPathname())) {
            return response()->json(['error' => 'File harus berupa gambar JPG, PNG, GIF, atau WEBP.'], 422);
        }

        $randomName = 'image_' . Str::random(10) . '.' . $ext;
        $path = public_path('assets/app/image-editor');
        if (! File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $file->move($path, $randomName);

        return response()->json(['url' => asset('assets/app/image-editor/' . $randomName)], 200);
    }

    public function deleteImage(Request $request)
    {
        $imagePath = $request->input('image_path');
        if ($imagePath && File::exists(public_path($imagePath))) {
            File::delete(public_path($imagePath));
            return response()->json(['success' => 'Image deleted successfully'], 200);
        }

        return response()->json(['error' => 'Image not found'], 404);
    }
}
