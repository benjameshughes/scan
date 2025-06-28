<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

class Invite extends Model
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'invited_by',
        'expires_at',
        'token',
        'user_id',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }

    public function token(): string
    {
        return $this->token;
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted()
    {
        return $this->accepted_at !== null;
    }

    public function routeNotificationForMail()
    {
        return $this->email;
    }
}
