<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecruitmentEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(app()->bound('brand') && brand()->has_recruitment, 404);
        return $next($request);
    }
}
