<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Exceptions\PostTooLargeException;

class FileHelper
{
    const MAX_FILE_SIZE = 10;
    const MAX_IMAGE_SIZE = 3;

    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'ico', 'svg', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'];

    const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

    const FORBIDDEN_EXTENSIONS = ['php', 'exe', 'sh', 'bat'];

    /**
     * Validasi file yang diupload
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @throws \Exception
     * @return bool
     */
    private static function validateFile($file)
    {
        if (!$file) {
            throw new \Exception('File tidak ditemukan');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, self::FORBIDDEN_EXTENSIONS)) {
            throw new \Exception('Ekstensi file ini tidak diizinkan untuk alasan keamanan.');
        }

        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new \Exception('Ekstensi file tidak diizinkan. Ekstensi yang diizinkan: ' . implode(', ', self::ALLOWED_EXTENSIONS));
        }

        if (!in_array($file->getMimeType(), self::ALLOWED_MIMES)) {
            throw new \Exception('Tipe file tidak diizinkan. Tipe yang diizinkan: ' . implode(', ', self::ALLOWED_EXTENSIONS));
        }

        $fileSize = $file->getSize() / 1024 / 1024;
        if (strpos($file->getMimeType(), 'image/') === 0 && $fileSize > self::MAX_IMAGE_SIZE) {
            throw new PostTooLargeException('Ukuran gambar terlalu besar. Maksimal ' . self::MAX_IMAGE_SIZE . 'MB');
        } elseif ($fileSize > self::MAX_FILE_SIZE) {
            throw new PostTooLargeException('Ukuran file terlalu besar. Maksimal ' . self::MAX_FILE_SIZE . 'MB');
        }

        return true;
    }

    /**
     * Validasi isi file
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @throws \Exception
     * @return bool
     */
    private static function validateFileContent($file)
    {
        $filePath = $file->getPathname();

        if (strpos($file->getMimeType(), 'image/') === 0) {
            if (!@getimagesize($filePath)) {
                throw new \Exception('Isi file tidak valid sebagai gambar.');
            }
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filePath);

        if (!in_array($mimeType, self::ALLOWED_MIMES)) {
            throw new \Exception('Isi file tidak valid. MIME yang terdeteksi: ' . $mimeType);
        }

        return true;
    }

    public static function saveFile($file, $folder = 'folder', $nameFile = 'name file')
    {
        try {
            // Validasi file
            self::validateFile($file);
            self::validateFileContent($file);

            $user = Auth::user();
            $user->update(['failed_upload_attempts' => 0]);

            $nameFile = strtolower(str_replace(' ', '_', $nameFile));
            $randomName = $nameFile . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

            $log = [
                'name' => $user->name,
                'email' => $user->email,
                'folder' => $folder,
                'file_name' => $randomName,
                'time' => now(),
                'type_file' => $file->getClientOriginalExtension(),
            ];

            Log::info('File diunggah.', $log);

            $path = public_path('assets/app/' . $folder);
            if (!File::exists($path)) {
                File::makeDirectory($path, 0777, true, true);
            }

            $file->move($path, $randomName);
            return 'assets/app/' . $folder . '/' . $randomName;
        } catch (\Exception $e) {
            $user = User::find(Auth::user()->id);

            $user->increment('failed_upload_attempts');

            if ($user->failed_upload_attempts >= 3) {
                $user->update(['status' => 'inactive']);
                Auth::logout();
                abort(500, '⚠️ Akses Ditangguhkan! Anda telah melebihi batas maksimum percobaan upload file yang tidak diizinkan (3/3). Akun Anda telah dinonaktifkan demi menjaga keamanan sistem kami. Silakan hubungi administrator untuk informasi lebih lanjut. <br> Specific' . $e->getMessage());
            } else {
                abort(500, '⚠️ Peringatan! File yang Anda coba unggah tidak diizinkan. Ini adalah percobaan ke-' . $user->failed_upload_attempts . ' dari 3 sebelum akun Anda dinonaktifkan demi keamanan sistem kami. <br> Specific: ' . $e->getMessage());
            }
        }
    }

    public static function deleteFile($filePath)
    {
        // ubah ke public/assets/app
        $absolutePath = public_path($filePath);

        if (File::exists($absolutePath)) {
            return File::delete($absolutePath);
        }

        return true;
    }

}
