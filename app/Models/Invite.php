<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
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
        if (empty($this->email)) {
            \Log::error('Invite routeNotificationForMail: email is empty', [
                'invite_id' => $this->id,
                'invite_data' => $this->toArray(),
            ]);
        }

        return $this->email;
    }
}
