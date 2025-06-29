<?php

namespace App\Actions\Dashboard;

use App\Actions\Contracts\Action;

class MarkNotificationAsRead implements Action
{
    private string $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function handle()
    {
        // Mark it as read please
        auth()->user()->unreadNotifications()->find($this->id)->markAsRead();
    }
}
