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

        if (! $product) {
            return;
        }

        // Get admin users
        $admins = User::with('roles')
            ->get()
            ->filter(fn ($user) => $user->roles->contains('name', 'admin'));

        // Notify each admin
        $admins->each(function ($admin) use ($product) {
            $admin->notify(new EmptyBayNotification($product));
        });
    }
}
