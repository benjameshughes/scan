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

    public array $scansByDate;

    // Mark notification as read
    public function markAsRead($id)
    {
        new MarkNotificationAsRead($id)->handle();

        $this->notifications = auth()->user()->unreadNotifications()->get();
    }

    // Mark all notifications as read
    public function readAll()
    {
        $notifications = auth()->user()->unreadNotifications();

        $notifications->each(function ($notification) {
            $notification->markAsRead();
        });

        $this->notifications = auth()->user()->unreadNotifications()->get();
    }

    /**
     * Redispatch all jobs that have not been submitted
     * Livewire is already loading the scans. Just filter the scans by submitted status
     * whereFalse is a macro is AppProvider
     */
    public function redispatch()
    {
        // Filter unsubmitted scans from the scan array
        $failedScans = $this->scans->whereFalse('submitted');

        // Dispatch all jobs
        $failedScans->each(function (Scan $scan) {
            SyncBarcode::dispatch($scan);
        });
    }

    public function markAsSubmitted(int $id)
    {
        // I assume I first need to find the ID of the scan to then pass the object of the scan to the action?
        $scan = Scan::where('id', $id)->first();

        // Then pass the scan variable to the action
        new MarkScanAsSubmitted($scan)->handle();

        $this->scans = Scan::all();
    }

    public function scansByDate(): Collection
    {
        // Get unsubmitted scans
        $scans = $this->scans;

        // Define the date range
        $startDate = Carbon::now()->subDays($this->scanDate);
        $endDate = Carbon::now();

        // Filter the scans by date
        $scans = $scans->filter(function ($scan) use ($startDate, $endDate) {
            return $scan->submitted_at->between($startDate, $endDate);
        });

        return $this->scansByDate = $scans;
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
