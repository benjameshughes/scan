<?php

namespace App\Livewire;

use App\Actions\SyncAllPendingScans;
use App\Models\Scan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class Dashboard extends Component
{
    public $notifications = [];

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
        $scans = Scan::where('submitted', false)->get();

        // Dispatch all jobs
        (new SyncAllPendingScans($scans))->handle();
    }

    public function mount()
    {
        $this->notifications = auth()->user()->unreadNotifications;
        $this->scans = Scan::all()->where('submitted', false);
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
