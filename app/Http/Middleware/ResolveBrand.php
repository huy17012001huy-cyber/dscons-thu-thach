<?php

namespace App\Http\Middleware;

use App\Models\Brand;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveBrand
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $brand = Brand::findByDomain($host);

        // Fallback to Website Thử Thách (id=1) in local/testing environments
        if (!$brand && in_array(app()->environment(), ['local', 'testing'])) {
            $brand = Brand::find(1);
        }

        if (!$brand) {
            abort(404, 'Community not found.');
        }

        app()->instance('brand', $brand);
        View::share('brand', $brand);

        return $next($request);
    }
}
