<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBadge extends Component
{
    public int $notificationCount = 0;

    #[On(['notification.markAsRead', 'notification.markAllAsRead'])]
    public function mount()
    {
//        $this->notificationCount = auth()->user()->unreadNotifications()->count() ?? 0;
    }
    public function render()
    {
        return view('livewire.notification-badge');
    }
}
