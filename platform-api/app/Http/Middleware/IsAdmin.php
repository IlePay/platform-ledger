<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['ADMIN', 'SUPER_ADMIN'])) {
            abort(403, 'Accès refusé');
        }

        return $next($request);
    }
}