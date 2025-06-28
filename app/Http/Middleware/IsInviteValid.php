<?php

namespace App\Http\Middleware;

use App\Models\Invite;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsInviteValid
{
    /**
     * Handle an incoming request.
     * This request will check the token from the url against the pending invites in the database.
     * If an invite has expired or been accepted then redirect to the login page
     * If it is still pending and not accepted show the form to set a password for the valid token
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // First get the invite object from the token
        $invite = Invite::where('token', $request->route('token'))->first();

        // Is the token a valid token?
        if($invite === null || $invite->expires_at->isPast() || $invite->isAccepted())
        {
            return redirect('login');
        }

        return $next($request);
    }
}
