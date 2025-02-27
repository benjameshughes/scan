<?php

namespace App\Livewire;

use App\Actions\SyncAllPendingScans;
use App\Jobs\SyncBarcode;
use App\Models\Scan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public $notifications = [];

    public Collection $scansByDate;

    public int $scanDate = 8;

    public Collection $scans;

    // Mark notification as read
    public function markAsRead($id)
    {
        $user = auth()->user();
        $notification = $user->unreadNotifications->find($id);

        $notification->markAsRead($id);

        // refresh notifications
        $this->notifications = auth()->user()->unreadNotifications;
    }

    // Mark all notifications as read
    public function readAll()
    {
        $notifications = collect(auth()->user()->unreadNotifications);

        $notifications->each(function ($notification) {
            $notification->markAsRead();
        });
    }

    // Redispatch all jobs that have not been submitted
    public function redispatch()
    {
        //Collect all scans that have not been submitted
        $failedScans = collect(Scan::where('submitted', false)->get());

        // Dispatch all jobs
        $failedScans->each(function ($scan) use ($failedScans) {
            SyncBarcode::dispatch($scan);
        })->chunk(10);
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
        $this->notifications = auth()->user()->unreadNotifications;
        $this->scans = Scan::all();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
