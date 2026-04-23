<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageController extends Controller
{
    /**
     * Upload file lên Cloudflare R2 qua server, trả về public URL.
     *
     * POST /api/teacher/storage/upload
     * Body: multipart/form-data  { file: <binary>, path?: string }
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|image|max:10240', // max 10MB, chỉ nhận ảnh
            'path' => 'nullable|string',
        ]);

        $customPath = trim($request->input('path', 'test'), '/');

        $file        = $request->file('file');
        $extension   = $file->getClientOriginalExtension();
        $safeName    = Str::uuid() . ($extension ? '.' . $extension : '');
        $storagePath = $customPath ? $customPath . '/' . $safeName : $safeName;

        // Stream file lên R2
        $disk = Storage::disk('s3');
        $disk->put($storagePath, file_get_contents($file->getRealPath()));

        // Tạo public URL từ AWS_URL
        $publicBase = config('filesystems.disks.s3.url');
        $publicUrl  = $publicBase
            ? rtrim($publicBase, '/') . '/' . ltrim($storagePath, '/')
            : $disk->url($storagePath);

        return ApiResponse::success([
            'public_url' => $publicUrl,
            'path'       => $storagePath,
        ], 'Upload thành công');
    }

    /**
     * Generate a presigned URL for uploading to Cloudflare R2.
     * (Giữ lại để backward compatibility)
     */
    public function getPresignedUrl(Request $request)
    {
        $request->validate([
            'path' => 'nullable|string',
        ]);

        $customPath = trim($request->input('path', 'uploads'), '/');
        $safeName   = (string) Str::uuid();
        $path       = $customPath ? $customPath . '/' . $safeName : $safeName;

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk       = Storage::disk('s3');
        $uploadData = $disk->temporaryUploadUrl($path, now()->addMinutes(60));

        $uploadUrl = is_array($uploadData) ? $uploadData['url'] : $uploadData;
        $headers   = is_array($uploadData) ? $uploadData['headers'] : [];

        $publicBase = config('filesystems.disks.s3.url');
        $publicUrl  = $publicBase
            ? rtrim($publicBase, '/') . '/' . ltrim($path, '/')
            : '/' . ltrim($path, '/');

        return ApiResponse::success([
            'upload_url' => $uploadUrl,
            'headers'    => $headers,
            'public_url' => $publicUrl,
        ], 'Get presigned URL successfully');
    }
}
