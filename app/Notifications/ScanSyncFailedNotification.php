<?php

namespace App\Notifications;

use App\Models\Scan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScanSyncFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Scan $scan,
        public string $errorMessage,
        public string $errorType
    ) {}

    public function via(object $notifiable): array
    {
        // Check if user has permission to receive scan-related notifications
        // Only users who can view scans should receive scan sync failure notifications
        if (! $notifiable->can('view scans')) {
            return [];
        }

        // All permitted users get database and mail notifications
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject("Scan Sync Failed - {$this->scan->barcode}")
            ->line('Your recent barcode scan encountered an issue and failed to sync with the inventory system.')
            ->line("Barcode: {$this->scan->barcode}")
            ->line("Error: {$this->errorMessage}")
            ->line("Scanned at: {$this->scan->created_at->format('Y-m-d H:i:s')}")
            ->action('View Scan Details', url("/admin/scans/{$this->scan->id}"))
            ->line('Please contact support if this issue persists.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'scan_sync_failed',
            'title' => 'Scan Sync Failed',
            'message' => "Your scan of '{$this->scan->barcode}' failed to sync: {$this->errorMessage}",
            'scan_id' => $this->scan->id,
            'barcode' => $this->scan->barcode,
            'error_type' => $this->errorType,
            'error_message' => $this->errorMessage,
            'scanned_at' => $this->scan->created_at,
            'action_url' => "/admin/scans/{$this->scan->id}",
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'scan_sync_failed',
            'title' => 'Scan Sync Failed',
            'message' => "Your scan of '{$this->scan->barcode}' failed to sync: {$this->errorMessage}",
            'scan_id' => $this->scan->id,
            'barcode' => $this->scan->barcode,
            'error_type' => $this->errorType,
            'error_message' => $this->errorMessage,
            'scanned_at' => $this->scan->created_at->toISOString(),
            'action_url' => "/admin/scans/{$this->scan->id}",
            'severity' => 'medium',
            'timestamp' => now()->toISOString(),
            'icon' => '⚠️',
        ]);
    }
}
