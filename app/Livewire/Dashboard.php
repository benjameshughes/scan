<?php

namespace App\Livewire;

use App\Models\Scan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public $notifications;

    public $scans;

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->find($id);

        if($notification)
        {
            $notification->markAsRead();
            $this->notifications = Auth::user()->unreadNotifications();
        }

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
