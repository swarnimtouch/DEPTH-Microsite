<?php

namespace App\Http\Middleware;

use App\Models\Course;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMicrositeAccessIsOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('login', 'login.post') || Auth::guard('web')->check()) {
            return $next($request);
        }

        if (Course::participantAccessEnabled()) {
            return $next($request);
        }

        return redirect()
            ->to(route('home') . '#register')
            ->with('success', 'Registrations are open. Microsite access will be enabled later.');
    }
}
