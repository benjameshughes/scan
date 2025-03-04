<?php

namespace App\Actions\Concerns;

use App\Models\User;
use Illuminate\Notifications\Notification;

trait SendNotifications {

    protected function NotifyAllUsers(Notification $notification)
    {
        $users = collect(User::all());

        $users->each(function ($user) use ($notification) {
            $user->notify($notification);
        })->chunk(10);
    }

    protected function NotifyUsers(Notification $notification, array $userEmails)
    {
        $users = collect(User::where('email', $userEmails));

        $users->each(function ($user) use ($notification) {
            $user->notify($notification);
        });
    }

}