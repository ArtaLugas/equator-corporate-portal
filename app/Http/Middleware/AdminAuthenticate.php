<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('admin')->check()) {
            // guest() stores the intended URL so AuthController can redirect
            // back to it after a successful login (->intended()).
            return redirect()->guest(route('admin.login'));
        }

        return $next($request);
    }
}
