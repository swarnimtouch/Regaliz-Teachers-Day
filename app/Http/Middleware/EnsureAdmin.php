<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! auth()->user()->status || ! in_array(auth()->user()->role, ['admin', 'super_admin'], true)) {
            auth()->logout();

            return redirect()->route('admin.login')->with('error', 'Please sign in with an active admin account.');
        }

        return $next($request);
    }
}
