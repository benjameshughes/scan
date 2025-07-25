<?php

namespace App\Notifications;

use App\Models\StockMovement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefillSyncFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public StockMovement $stockMovement,
        public string $errorMessage,
        public string $errorType
    ) {}

    public function via(object $notifiable): array
    {
        // Check if user has permission to receive stock movement notifications
        // Only users who can view stock movements should receive refill sync failure notifications
        if (!$notifiable->can('view stock movements')) {
            return [];
        }

        // All permitted users get database and mail notifications
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject("Refill Operation Failed - {$this->stockMovement->product->sku}")
            ->line('A refill bay operation has failed to sync with Linnworks.')
            ->line("Product: {$this->stockMovement->product->name} ({$this->stockMovement->product->sku})")
            ->line("Quantity: {$this->stockMovement->quantity}")
            ->line("From: {$this->stockMovement->from_location_code}")
            ->line("To: {$this->stockMovement->to_location_code}")
            ->line("Error: {$this->errorMessage}")
            ->action('View Stock Movement', url("/admin/stock-movements/{$this->stockMovement->id}"))
            ->line('Please review and retry the operation manually if needed.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'refill_sync_failed',
            'title' => 'Refill Operation Failed',
            'message' => "Failed to sync refill for {$this->stockMovement->product->sku}: {$this->errorMessage}",
            'stock_movement_id' => $this->stockMovement->id,
            'product_sku' => $this->stockMovement->product->sku,
            'error_type' => $this->errorType,
            'error_message' => $this->errorMessage,
            'action_url' => "/admin/stock-movements/{$this->stockMovement->id}",
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
            'type' => 'refill_sync_failed',
            'title' => 'Refill Operation Failed',
            'message' => "Failed to sync refill for {$this->stockMovement->product->sku}: {$this->errorMessage}",
            'stock_movement_id' => $this->stockMovement->id,
            'product_sku' => $this->stockMovement->product->sku,
            'error_type' => $this->errorType,
            'error_message' => $this->errorMessage,
            'action_url' => "/admin/stock-movements/{$this->stockMovement->id}",
            'severity' => 'medium',
            'timestamp' => now()->toISOString(),
            'icon' => '🚨',
        ]);
    }
}
