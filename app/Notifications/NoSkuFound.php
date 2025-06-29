<?php

namespace App\Notifications;

use App\Models\Scan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NoSkuFound extends Notification
{
    use Queueable;

    public Scan $scan;

    /**
     * Create a new notification instance.
     */
    public function __construct(Scan $scan)
    {
        $this->scan = $scan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
        // Respect the users settings for notifications
        //        $channels = [];
        //
        //        if($notifiable->notification_emails) {
        //            $channels[] = 'mail';
        //        }
        //        if($notifiable->notification_database) {
        //            $channels[] = 'database';
        //        }
        //
        //        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("No Sku Found For Scan {$this->scan->id}")
            ->line('There was no SKU found for this scan.')
            ->action('View The Scan', url(route('scan.show', ['scan' => $this->scan->id])))
            ->line('Please leave Ben alone about this');
    }

    /**
     * Create a database notification
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'No Sku found',
            'scan_id' => $this->scan->id,
            'barcode' => $this->scan->barcode,
            'date' => $this->scan->created_at,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
        ];
    }

    /**
     * Get the notification type.
     */
    public function databaseType(): string
    {
        return 'no-sku-found';
    }
}
