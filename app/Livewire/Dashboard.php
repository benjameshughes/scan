<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public $notifications;

    public function markAsRead($notificationId)
    {
        $notification = auth()->user()->notifications->find($notificationId);
        $notification->markAsRead();
        $this->dispatch('markedAsRead');
    }

    public function mount()
    {
        $this->notifications = auth()->user()->unreadNotifications;
    }
    public function render()
    {
        return view('livewire.dashboard');
    }
}
