<?php

namespace App\Models;

use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SchoolDomain extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'school_id',
        'domain',
        'is_primary',
        'status',
        'verified_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'verified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (SchoolDomain $schoolDomain) {
            $schoolDomain->domain = self::normalizeSubdomain($schoolDomain->domain);
        });
    }

    public static function normalizeDomain(?string $value): string
    {
        $value = Str::lower(trim((string) $value));
        $value = preg_replace('/^https?:\/\//', '', $value) ?? $value;
        $value = explode('/', $value, 2)[0];
        $value = explode(':', $value, 2)[0];

        return trim($value, ". \t\n\r\0\x0B");
    }

    public static function normalizeSubdomain(?string $value): string
    {
        $value = self::normalizeDomain($value);

        return explode('.', $value, 2)[0] ?? '';
    }

    public static function subdomainFromHost(?string $host): string
    {
        return self::normalizeSubdomain($host);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
