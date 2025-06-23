<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // First, check if a user is authenticated
        if (!Auth::check()) {
            // If not, redirect to the admin login page
            return redirect()->route('admin.login');
        }

        // Now, check if the authenticated user is an admin using our helper function
        if (!Auth::user()->isAdmin()) {
            // If not an admin, forbid access
            abort(403, 'Unauthorized Access.');
        }

        // If the user is an admin, allow the request to proceed
        return $next($request);
    }
}
