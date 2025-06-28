<?php

namespace App\Livewire;

use App\Actions\Dashboard\MarkNotificationAsRead;
use App\Actions\MarkScanAsSubmitted;
use App\Jobs\SyncBarcode;
use App\Models\Scan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public Collection $notifications;
    public int $retryCount = 0;

    // Mark notification as read
    public function markAsRead($id)
    {
        new MarkNotificationAsRead($id)->handle();

        $this->notifications = auth()->user()->unreadNotifications()->get();
        $this->dispatch('notification.markAsRead');
    }

    // Mark all notifications as read
    public function readAll()
    {
        $notifications = auth()->user()->unreadNotifications();

        $notifications->each(function ($notification) {
            $notification->markAsRead();
        });

        $this->dispatch('notification.markAllAsRead');

        $this->notifications = auth()->user()->unreadNotifications()->get();
    }

    /**
     * Redispatch all jobs that have not been submitted
     * Only do the scans with a product model
     */
    public function redispatch()
    {
        $this->retryCount = 0;

        // Process in chunks to avoid memory issues
        Scan::whereFalse('submitted')
            ->with('product')
            ->get()
            ->chunk(100)
            ->each(function ($chunk) {
                $chunk->each(function ($scan) {
                    if ($scan->product) {
                        SyncBarcode::dispatch($scan);
                        $this->retryCount++;
                    }
                });
            });

        return $this->retryCount;
    }


    public function markAsSubmitted(int $id)
    {
        $scan = Scan::findOrFail($id);

        // Pass the scan variable to the action
        new MarkScanAsSubmitted($scan)->handle();
    }

    public function mount()
    {
        $this->notifications = auth()->user()->unreadNotifications()->get();
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'scans' => Scan::query()->whereNot('status','completed')->paginate(5),
        ]);
    }
}
