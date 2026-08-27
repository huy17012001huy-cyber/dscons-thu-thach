<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EngineerOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->is_admin || $request->user()?->isEngineer(), 403);

        return $next($request);
    }
}
