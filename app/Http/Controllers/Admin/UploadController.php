<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function uploadImage(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file'], 400);
        }

        $file = $request->file('file');
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        $ext = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();

        if (!in_array($ext, $allowedExt) || !in_array($mime, $allowedMimes)) {
            Log::channel('security')->warning('Invalid file upload', ['ip' => $request->ip()]);
            return response()->json(['error' => 'Invalid file type'], 400);
        }

        if (!@getimagesize($file->getPathname())) {
            return response()->json(['error' => 'Invalid image'], 400);
        }

        $name = 'img_' . Str::random(10) . '.' . $ext;
        $path = public_path('assets/uploads');

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $file->move($path, $name);
        return response()->json(['url' => asset('assets/uploads/' . $name)]);
    }

    public function deleteImage(Request $request)
    {
        $imagePath = $request->input('image_path');
        $imagePath = str_replace(['../', '..\\'], '', $imagePath);

        $allowedPaths = ['assets/uploads/', 'assets/images/'];
        $isAllowed = false;
        foreach ($allowedPaths as $allowed) {
            if (strpos($imagePath, $allowed) === 0) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            Log::channel('security')->critical('Unauthorized file deletion', [
                'path' => $imagePath, 'ip' => $request->ip()
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $fullPath = public_path($imagePath);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
            return response()->json(['success' => 'Deleted']);
        }

        return response()->json(['error' => 'Not found'], 404);
    }
}