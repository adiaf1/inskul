<?php

namespace App\Support;

use App\Models\School;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SchoolFileStorage
{
    public static function store(UploadedFile $file, School $school, string $folder, string $prefix): string
    {
        $relativeDirectory = self::relativeDirectory($school, $folder);
        $absoluteDirectory = storage_path('app/public/'.$relativeDirectory);

        File::ensureDirectoryExists($absoluteDirectory);

        $extension = strtolower($file->extension() ?: $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg');
        $filename = Str::slug($prefix).'-'.Str::uuid()->toString().'.'.$extension;

        $file->move($absoluteDirectory, $filename);

        return $relativeDirectory.'/'.$filename;
    }

    public static function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (str_contains($path, '/') && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return;
        }

        $publicPath = str_starts_with($path, 'uploads/')
            ? public_path($path)
            : public_path('assets/img/avatars/'.$path);

        if (is_file($publicPath)) {
            File::delete($publicPath);
        }
    }

    public static function url(?string $path, string $fallback = 'assets/img/avatars/default.png'): string
    {
        if (! $path) {
            return asset($fallback);
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, ['assets/', 'uploads/'])) {
            return asset($path);
        }

        if (! str_contains($path, '/')) {
            return asset('assets/img/avatars/'.$path);
        }

        return asset('storage/'.$path);
    }

    private static function relativeDirectory(School $school, string $folder): string
    {
        $npsn = Str::slug($school->npsn ?: 'tanpa-npsn');
        $name = Str::slug($school->name ?: 'sekolah-'.$school->id);

        return 'schools/'.$npsn.'-'.$name.'/'.trim($folder, '/');
    }
}
