<?php

namespace App\Actions\Concerns;

use App\Models\User;
use Illuminate\Notifications\Notification;

trait SendNotifications {

    /**
     *
     * Send out a notification to all users.
     * The notification can be specified as an argument.
     *
     * @param Notification $notification
     * @return void
     */
    protected function notifyAllUsers(Notification $notification)
    {
        $users = User::all();

        $users->each(function ($user) use ($notification) {
            $user->notify($notification);
        })->chunk(10);
    }

    /**
     *
     * Send out a notification to certain users.
     * The users is an array which can be an argument.
     * The notification can be specified as an argument.
     *
     * @param Notification $notification
     * @param array $userEmails
     * @return void
     */
    protected function notifyUsers(Notification $notification, array $userEmails)
    {
        $users = collect(User::where('email', $userEmails));

        $users->each(function ($user) use ($notification) {
            $user->notify($notification);
        });
    }

}