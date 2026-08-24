<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CvEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(app()->bound('brand') && brand()->has_cv, 404);
        return $next($request);
    }
}
