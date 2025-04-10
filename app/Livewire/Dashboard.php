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

    // Remove the public scans property
    // public Collection $scans;

    public array $scansByDate = [];
    public $scanDate = 7; // Added default value for scanDate

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
     */
    public function redispatch()
    {
        // Get unsubmitted scans directly from the database
        $failedScans = Scan::whereFalse('submitted')->get();

        // Dispatch all jobs
        $failedScans->each(function (Scan $scan) {
            SyncBarcode::dispatch($scan);
        });
    }

    public function markAsSubmitted(int $id)
    {
        $scan = Scan::findOrFail($id);

        // Pass the scan variable to the action
        new MarkScanAsSubmitted($scan)->handle();

        // No need to reload all scans, Livewire will refresh the component
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
        // Remove loading all scans here
    }

    public function render()
    {
        // Get paginated scans in the render method
        $scans = Scan::paginate(10);

        return view('livewire.dashboard', [
            'scans' => $scans
        ]);
    }
}
