<?php

namespace App\Jobs;

use App\Models\Scan;
use App\Models\User;
use App\Notifications\EmptyBayNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EmptyBayJob implements ShouldQueue
{
    use Queueable;

    protected int $barcode;

    /**
     * Create a new job instance.
     */
    public function __construct(\App\DTOs\EmptyBayDTO $emptyBayDTO)
    {
        $this->barcode = $emptyBayDTO->barcode;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        // Can I instantiate a new scan model to use the relationship?
        $tempScan = new Scan(['barcode' => $this->barcode]);

        $product = $tempScan->product;

        if(!$product)
        {
            return;
        }

        // Dispatch notification with SKU
        $users = User::with('roles')->get()->filter(
            fn($user) => $user->roles->where('name', 'admin')->toArray()
        );

        $users->each(function ($user) use ($product) {
            $user->notify(new EmptyBayNotification($product));
        });
        // $user->notify(new \App\Notifications\EmptyBayNotification($product));
    }
}
