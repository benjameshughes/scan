<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $refillUrl = route('scan.scan', [
            'action' => 'refill',
            'barcode' => $this->product->barcode
        ]);

        return (new MailMessage)
            ->subject('Empty Bay Alert - Immediate Refill Required')
            ->greeting("Bay Empty: {$this->product->name}")
            ->line("The bay for **{$this->product->sku}** is now empty and requires immediate attention.")
            ->line("Product: {$this->product->name}")
            ->line("SKU: {$this->product->sku}")
            ->line("Barcode: {$this->product->barcode}")
            ->action('Refill Bay Now', $refillUrl)
            ->line('Click the button above to go directly to the scanner and start the refill process.')
            ->salutation('Thanks for keeping our inventory stocked!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
