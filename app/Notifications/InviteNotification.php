<?php

namespace App\Notifications;

use App\Mail\InviteEmail;
use App\Models\Invite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class InviteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $invite;

    /**
     * Create a new notification instance.
     */
    public function __construct(Invite $invite)
    {
        $this->invite = $invite;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $settings = $notifiable->settings ?? [];
        $channels = ['mail']; // Always send invitation emails

        if ($settings['notification_push'] ?? true) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): InviteEmail
    {
        return new InviteEmail($this->invite);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'invite_sent',
            'invite_id' => $this->invite->id,
            'email' => $this->invite->email,
            'expires_at' => $this->invite->expires_at->toISOString(),
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'invite_sent',
            'title' => 'Invitation Sent',
            'message' => "Invitation sent to {$this->invite->email}",
            'invite_id' => $this->invite->id,
            'email' => $this->invite->email,
            'expires_at' => $this->invite->expires_at->toISOString(),
            'severity' => 'low',
            'timestamp' => now()->toISOString(),
            'icon' => '📧',
        ]);
    }
}
