<?php

namespace App\Livewire;

use App\Models\Scan;
use Livewire\Component;

class Dashboard extends Component
{
    public $notifications;

    public $scans;

    public function markAsRead($notificationId)
    {
        $notification = auth()->user()->notifications->find($notificationId);
        $notification->markAsRead();
        $this->dispatch('markedAsRead');
    }

    public function mount()
    {
        $this->notifications = auth()->user()->unreadNotifications;
        $this->scans = Scan::all()  ->where('submitted', false);
    }
    public function render()
    {
        return view('livewire.dashboard');
    }
}
