<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadHelper
{
    /**
     * Upload foto dengan nama berdasarkan kode.
     * 
     * @param UploadedFile $file
     * @param string $folder  contoh: 'materials'
     * @param string $kode    contoh: 'MAT-0001'
     * @param string|null $oldPath  path lama untuk dihapus
     * @return string path yang disimpan
     */
    public static function uploadPhoto(
        UploadedFile $file,
        string $folder,
        string $kode,
        ?string $oldPath = null
    ): string {
        // Hapus file lama kalau ada
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // Nama file: MAT-0001.jpg
        $extension = $file->getClientOriginalExtension();
        $filename = $kode . '.' . $extension;
        $path = "{$folder}/{$filename}";

        // Simpan
        Storage::disk('public')->putFileAs($folder, $file, $filename);

        return $path;
    }

    /**
     * Hapus foto.
     */
    public static function deletePhoto(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}