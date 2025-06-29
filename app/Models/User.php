<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    use HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'settings' => 'array',
    ];

    protected static function booted(): void
    {
        static::updated(function ($user) {
            // If users name is changed, clear the avatar cache
            if ($user->isDirty('name')) {
                Cache::forget("user:{$user->id}:avatar_url");
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function scans(): belongsToMany
    {
        return $this->belongsToMany(Scan::class);
    }

    public function avatarAttribute(): string
    {
        $cacheKey = "user:{$this->id}:avatar_url";

        return Cache::remember($cacheKey, now()->addWeek(), function () {
            $name = urlencode($this->name);

            return "https://ui-avatars.com/api/?name={$name}&background=random";
        });
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    /**
     * Get the settings attribute with defaults
     */
    public function getSettingsAttribute($value)
    {
        $defaults = [
            'notification_emails' => true,
            'notification_database' => true,
        ];

        if (! $value) {
            return $defaults;
        }

        $settings = json_decode($value, true);

        return array_merge($defaults, $settings);
    }

    public function invite(): HasOne
    {
        return $this->hasOne(Invite::class, 'user_id');
    }

    public function status(): bool
    {
        return $this->status;
    }

    /**
     * Set the settings attribute
     */
    public function setSettingsAttribute($value)
    {
        $this->attributes['settings'] = is_array($value) ? json_encode($value) : $value;
    }
}
