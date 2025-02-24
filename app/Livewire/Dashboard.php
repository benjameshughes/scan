<?php

namespace App\Livewire;

use App\Actions\SyncAllPendingScans;
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

    public $scans;

    // Mark notification as read
    public function markAsRead($id)
    {
        $user = auth()->user();
        $notification = $user->unreadNotifications->find($id);

        $notification->markAsRead($id);

        // refresh notifications
        $this->notifications = auth()->user()->unreadNotifications;
    }

    // Redispatch all jobs that have not been submitted
    public function redispatch()
    {
        // Collect all scans that have not been submitted
//        $scans = Scan::where('submitted', false)->get();
//
//        // Dispatch all jobs
//        SyncAllPendingScans::handle($scans);

        // Sleep for 5 seconds to test the spin animation
        sleep (5);
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
