<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuidPrimaryKey, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (! empty($user->username)) {
                $user->username = self::normalizeUsername($user->username);

                return;
            }

            $source = $user->email ? Str::before($user->email, '@') : $user->name;
            $user->username = self::uniqueUsername($source ?: 'user');
        });

        static::updating(function (User $user) {
            if ($user->isDirty('username') && ! empty($user->username)) {
                $user->username = self::normalizeUsername($user->username);
            }
        });
    }

    public static function normalizeUsername(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->replaceMatches('/[^a-z0-9._-]+/', '.')
            ->trim('.-_')
            ->limit(40, '')
            ->value() ?: 'user';
    }

    public static function uniqueUsername(string $source, ?string $ignoreUserId = null): string
    {
        $base = self::normalizeUsername($source);
        $username = $base;
        $counter = 2;

        while (self::query()
            ->where('username', $username)
            ->when($ignoreUserId, fn ($query) => $query->whereKeyNot($ignoreUserId))
            ->exists()) {
            $username = $base.$counter;
            $counter++;
        }

        return $username;
    }

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class)
            ->using(SchoolUser::class)
            ->withPivot(['role_id', 'status'])
            ->withTimestamps();
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }
}
