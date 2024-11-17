<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->userIsAdmin()) {
            return $next($request);
        }

        abort(Response::HTTP_FORBIDDEN, 'Unauthorized access.');
    }

    private function userIsAdmin(): bool
    {
        return Auth::check() && Auth::user()->is_admin;
    }
}
