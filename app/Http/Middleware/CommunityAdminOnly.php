<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CommunityAdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isCommunityAdmin($this->brandId($request)), 403);

        return $next($request);
    }

    private function brandId(Request $request): ?int
    {
        $community = $request->route('community');
        return $community?->id ?? (app()->bound('brand') ? brand()->id : null);
    }
}
