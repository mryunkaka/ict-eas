<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicFileUpload
{
    /**
     * Store with stable filename (based on original name).
     * If the same filename already exists in the target directory, reuse it.
     *
     * @return array{name:string,path:string,size:int|false,mime:string}
     */
    public static function storeStableOrReuse(UploadedFile $file, string $directory, int $displayLength = 255): array
    {
        $directory = trim($directory, '/');
        $originalBase = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slugBase = Str::slug($originalBase);
        $slugBase = $slugBase !== '' ? $slugBase : 'file';
        $slugBase = Str::limit($slugBase, 80, '');

        if (self::shouldCompressImage($file)) {
            $storedName = $slugBase.'.jpg';
            $displayName = self::buildDisplayName($file, $displayLength, 'jpg');
            $path = $directory.'/'.$storedName;

            if (Storage::disk('public')->exists($path)) {
                return [
                    'name' => $displayName,
                    'path' => $path,
                    'size' => Storage::disk('public')->size($path),
                    'mime' => Storage::disk('public')->mimeType($path) ?: 'image/jpeg',
                ];
            }

            $binary = self::compressImageBinary($file, 460 * 1024);
            Storage::disk('public')->put($path, $binary);

            return [
                'name' => $displayName,
                'path' => $path,
                'size' => Storage::disk('public')->size($path),
                'mime' => 'image/jpeg',
            ];
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'dat');
        $storedName = $slugBase.'.'.$extension;
        $displayName = self::buildDisplayName($file, $displayLength, $extension);
        $path = $directory.'/'.$storedName;

        if (Storage::disk('public')->exists($path)) {
            return [
                'name' => $displayName,
                'path' => $path,
                'size' => Storage::disk('public')->size($path),
                'mime' => Storage::disk('public')->mimeType($path) ?: ($file->getClientMimeType() ?: 'application/octet-stream'),
            ];
        }

        $file->storeAs($directory, $storedName, 'public');

        return [
            'name' => $displayName,
            'path' => $path,
            'size' => Storage::disk('public')->size($path),
            'mime' => $file->getClientMimeType() ?: 'application/octet-stream',
        ];
    }

    /**
     * Finalize a temporary uploaded file (public disk) into a target directory.
     * - If a stable target filename already exists, reuse it and delete temp.
     * - If temp does not exist, return null.
     *
     * @return array{name:string,path:string,size:int|false,mime:string}|null
     */
    public static function finalizeTempToStableOrReuse(string $tempPath, string $originalName, string $directory, int $displayLength = 255): ?array
    {
        $tempPath = ltrim($tempPath, '/');
        $directory = trim($directory, '/');

        if ($tempPath === '' || ! Storage::disk('public')->exists($tempPath)) {
            return null;
        }

        $base = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $base = $base !== '' ? $base : 'file';
        $base = Str::limit($base, 80, '');

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION) ?: 'dat');
        $mime = Storage::disk('public')->mimeType($tempPath) ?: 'application/octet-stream';

        if (str_starts_with(strtolower($mime), 'image/')) {
            $extension = 'jpg';
            $mime = 'image/jpeg';
        }

        $storedName = $base.'.'.$extension;
        $path = $directory.'/'.$storedName;
        $displayName = Str::limit(Str::slug($base), max($displayLength - strlen($extension) - 1, 1), '').'.'.$extension;

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($tempPath);

            return [
                'name' => $displayName,
                'path' => $path,
                'size' => Storage::disk('public')->size($path),
                'mime' => Storage::disk('public')->mimeType($path) ?: $mime,
            ];
        }

        Storage::disk('public')->move($tempPath, $path);

        return [
            'name' => $displayName,
            'path' => $path,
            'size' => Storage::disk('public')->size($path),
            'mime' => Storage::disk('public')->mimeType($path) ?: $mime,
        ];
    }

    /**
     * @return array{name:string,path:string,size:int|false,mime:string}
     */
    public static function store(UploadedFile $file, string $directory, int $displayLength = 255, ?string $prefix = null): array
    {
        if (self::shouldCompressImage($file)) {
            return self::storeCompressedImage($file, $directory, $displayLength, $prefix);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'dat');
        $storedName = self::buildStoredName($file, $extension, $prefix);
        $displayName = self::buildDisplayName($file, $displayLength, $extension);
        $path = $file->storeAs($directory, $storedName, 'public');

        return [
            'name' => $displayName,
            'path' => $path,
            'size' => $file->getSize(),
            'mime' => $file->getClientMimeType() ?: 'application/octet-stream',
        ];
    }

    protected static function shouldCompressImage(UploadedFile $file): bool
    {
        $mime = strtolower((string) ($file->getClientMimeType() ?: ''));

        return str_starts_with($mime, 'image/') && function_exists('imagecreatefromstring');
    }

    /**
     * @return array{name:string,path:string,size:int|false,mime:string}
     */
    protected static function storeCompressedImage(UploadedFile $file, string $directory, int $displayLength, ?string $prefix): array
    {
        $binary = self::compressImageBinary($file, 460 * 1024);
        $storedName = self::buildStoredName($file, 'jpg', $prefix);
        $displayName = self::buildDisplayName($file, $displayLength, 'jpg');
        $path = trim($directory, '/').'/'.$storedName;

        Storage::disk('public')->put($path, $binary);

        return [
            'name' => $displayName,
            'path' => $path,
            'size' => Storage::disk('public')->size($path),
            'mime' => 'image/jpeg',
        ];
    }

    protected static function compressImageBinary(UploadedFile $file, int $targetBytes): string
    {
        $source = @file_get_contents($file->getRealPath());

        if ($source === false) {
            abort(500, 'Gagal membaca file gambar.');
        }

        $image = @imagecreatefromstring($source);

        if ($image === false) {
            abort(500, 'Format gambar tidak didukung.');
        }

        $image = self::applyExifOrientation($file, $image);
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);
        $bestBinary = '';

        foreach ([2560, 2200, 1920, 1600, 1365, 1200, 1024] as $maxDimension) {
            $workingImage = self::resizeToMaxDimension($image, $originalWidth, $originalHeight, $maxDimension);

            foreach ([90, 86, 82, 78, 74, 70, 66, 62] as $quality) {
                $candidate = self::encodeJpeg($workingImage, $quality);

                if ($candidate === '') {
                    continue;
                }

                if ($bestBinary === '' || strlen($candidate) < strlen($bestBinary)) {
                    $bestBinary = $candidate;
                }

                if (strlen($candidate) <= $targetBytes) {
                    if ($workingImage !== $image) {
                        imagedestroy($workingImage);
                    }
                    imagedestroy($image);

                    return $candidate;
                }
            }

            if ($workingImage !== $image) {
                imagedestroy($workingImage);
            }
        }

        imagedestroy($image);

        if ($bestBinary === '') {
            abort(500, 'Gagal mengompres gambar.');
        }

        return $bestBinary;
    }

    protected static function applyExifOrientation(UploadedFile $file, \GdImage $image): \GdImage
    {
        $realPath = $file->getRealPath();
        $mime = strtolower((string) ($file->getClientMimeType() ?: ''));

        if ($realPath === false || ! function_exists('exif_read_data') || ! in_array($mime, ['image/jpeg', 'image/jpg'], true)) {
            return $image;
        }

        $exif = @exif_read_data($realPath);
        $orientation = (int) ($exif['Orientation'] ?? 1);

        return match ($orientation) {
            3 => imagerotate($image, 180, 0) ?: $image,
            6 => imagerotate($image, -90, 0) ?: $image,
            8 => imagerotate($image, 90, 0) ?: $image,
            default => $image,
        };
    }

    protected static function resizeToMaxDimension(\GdImage $image, int $width, int $height, int $maxDimension): \GdImage
    {
        if ($width <= $maxDimension && $height <= $maxDimension) {
            return $image;
        }

        $scale = min($maxDimension / $width, $maxDimension / $height);
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $resized = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($resized === false) {
            abort(500, 'Gagal menyiapkan kompres gambar.');
        }

        $background = imagecolorallocate($resized, 255, 255, 255);
        imagefill($resized, 0, 0, $background);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        return $resized;
    }

    protected static function encodeJpeg(\GdImage $image, int $quality): string
    {
        ob_start();
        imageinterlace($image, true);
        imagejpeg($image, null, $quality);
        $binary = ob_get_clean();

        return is_string($binary) ? $binary : '';
    }

    protected static function buildStoredName(UploadedFile $file, string $extension, ?string $prefix = null): string
    {
        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $baseName = $baseName !== '' ? $baseName : 'file';
        $baseName = Str::limit($baseName, 40, '');
        $prefixPart = $prefix ? trim($prefix, '-').'_' : '';

        return $prefixPart.$baseName.'_'.Str::lower(Str::random(8)).'.'.$extension;
    }

    protected static function buildDisplayName(UploadedFile $file, int $displayLength, string $extension): string
    {
        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $baseName = $baseName !== '' ? $baseName : 'file';
        $budget = max($displayLength - strlen($extension) - 1, 1);
        $displayBase = Str::limit($baseName, $budget, '');

        return $displayBase.'.'.$extension;
    }
}
