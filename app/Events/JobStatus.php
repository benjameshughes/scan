<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobStatus
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $status;

    public $job;

    /**
     * Create a new event instance.
     */
    public function __construct($status, $job)
    {
        $this->status = $status;
        $this->job = $job;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('barcode-sync'),
        ];
    }

    public function broadcasrtWith(): array
    {
        return [
            'status' => $this->status,
        ];
    }
}
