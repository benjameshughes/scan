<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class hasNotifications
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()) {
            $notifications = auth()->user()->notifications;
            $unreadNotifications = $notifications->filter->unreadNotifications;
            $readNotifications = $notifications->filter->readNotifications;
            view()->share('notifications', $notifications);
            view()->share('unreadNotifications', $unreadNotifications);
        }

        return $next($request);
    }
}
