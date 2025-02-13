<?php

namespace App\Livewire;

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
