<?php

namespace App\Notifications;

use App\Models\Product;
use App\Services\LinnworksApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class EmptyBayNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Product $product;

    /**
     * Create a new notification instance.
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Check if user has permission to receive bay refill notifications
        // Only users who can refill bays should receive empty bay notifications
        if (! $notifiable->can('refill bays')) {
            return [];
        }

        // All permitted users get database and mail notifications
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $refillUrl = route('scan.scan', [
            'action' => 'refill',
            'barcode' => $this->product->barcode,
        ]);

        $message = (new MailMessage)
            ->subject('Empty Bay Alert - Immediate Refill Required')
            ->greeting("Bay Empty: {$this->product->name}")
            ->line("The bay for **{$this->product->sku}** is now empty and requires immediate attention.")
            ->line("Product: {$this->product->name}")
            ->line("SKU: {$this->product->sku}")
            ->line("Barcode: {$this->product->barcode}");

        // Get location stock information
        try {
            $linnworksService = app(LinnworksApiService::class);
            $locations = $linnworksService->getStockLocationsByProduct($this->product->sku);

            if (! empty($locations)) {
                // Sort locations by stock level (highest first) for better visibility
                usort($locations, function ($a, $b) {
                    $stockA = $a['StockLevel'] ?? $a['Quantity'] ?? $a['Available'] ?? $a['Stock'] ?? 0;
                    $stockB = $b['StockLevel'] ?? $b['Quantity'] ?? $b['Available'] ?? $b['Stock'] ?? 0;

                    return $stockB <=> $stockA;
                });

                $locationsWithStock = array_filter($locations, function ($location) {
                    $stockLevel = $location['StockLevel'] ?? $location['Quantity'] ?? $location['Available'] ?? $location['Stock'] ?? 0;

                    return $stockLevel > 0;
                });

                if (! empty($locationsWithStock)) {
                    $message->line('**Available Stock Locations:**');

                    foreach ($locationsWithStock as $location) {
                        $locationData = $location['Location'] ?? $location;
                        $locationName = $locationData['LocationName'] ?? $locationData['Name'] ?? 'Unknown Location';
                        $stockLevel = $location['StockLevel'] ?? $location['Quantity'] ?? $location['Available'] ?? $location['Stock'] ?? 0;

                        $message->line("• **{$locationName}** - {$stockLevel} units available");
                    }

                    Log::channel('inventory')->info('Empty bay notification sent with location stock', [
                        'product_sku' => $this->product->sku,
                        'locations_with_stock' => count($locationsWithStock),
                        'total_locations_checked' => count($locations),
                    ]);
                } else {
                    $message->line('**No stock found at other locations.**');

                    Log::channel('inventory')->info('Empty bay notification sent - no stock at other locations', [
                        'product_sku' => $this->product->sku,
                        'total_locations_checked' => count($locations),
                    ]);
                }
            } else {
                $message->line('**No stock found at other locations.**');
            }
        } catch (\Exception $e) {
            // If location lookup fails, continue without location info
            $message->line('**Location stock information temporarily unavailable.**');

            Log::channel('inventory')->error('Failed to fetch location stock for empty bay notification', [
                'product_sku' => $this->product->sku,
                'error' => $e->getMessage(),
            ]);
        }

        return $message
            ->action('Refill Bay Now', $refillUrl)
            ->line('Click the button above to go directly to the scanner and start the refill process.')
            ->salutation('Thanks for keeping our inventory stocked!');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'empty_bay',
            'title' => 'Bay Empty Alert',
            'message' => "The bay for {$this->product->name} ({$this->product->sku}) is now empty and requires immediate refill",
            'product_id' => $this->product->id,
            'product_sku' => $this->product->sku,
            'product_name' => $this->product->name,
            'barcode' => $this->product->barcode,
            'action_url' => route('scan.scan', [
                'action' => 'refill',
                'barcode' => $this->product->barcode,
            ]),
            'severity' => 'high',
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
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
            'type' => 'empty_bay',
            'title' => 'Bay Empty Alert',
            'message' => "The bay for {$this->product->name} ({$this->product->sku}) is now empty and requires immediate refill",
            'product_id' => $this->product->id,
            'product_sku' => $this->product->sku,
            'product_name' => $this->product->name,
            'barcode' => $this->product->barcode,
            'action_url' => route('scan.scan', [
                'action' => 'refill',
                'barcode' => $this->product->barcode,
            ]),
            'severity' => 'high',
            'timestamp' => now()->toISOString(),
            'icon' => '📦',
        ]);
    }
}
