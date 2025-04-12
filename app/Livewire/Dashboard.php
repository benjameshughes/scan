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
    public Collection $scans;
    public int $retryCount = 0;

    public array $scansByDate = [];
    public $scanDate = 7; // Added default value for scanDate

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
     */
    public function redispatch()
    {
        // Get unsubmitted scans directly from the database
        $failedScans = Scan::whereFalse('submitted')->get();

        // Dispatch all jobs
        $failedScans->each(function (Scan $scan) {
            if($scan->product)
            {
                SyncBarcode::dispatch($scan);
                $this->retryCount++;
            }
        })->chunk(10);
    }

    public function markAsSubmitted(int $id)
    {
        $scan = Scan::findOrFail($id);

        // Pass the scan variable to the action
        new MarkScanAsSubmitted($scan)->handle();
    }

    public function scansByDate(): Collection
    {
        // Get scans directly from the database
        $scans = Scan::all();

        // Define the date range
        $startDate = Carbon::now()->subDays($this->scanDate);
        $endDate = Carbon::now();

        // Filter the scans by date
        $scans = $scans->filter(function ($scan) use ($startDate, $endDate) {
            return $scan->submitted_at->between($startDate, $endDate);
        });

        return $scans;
    }

    public function mount()
    {
        $this->notifications = auth()->user()->unreadNotifications()->get();
        $this->scans = Scan::all();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
