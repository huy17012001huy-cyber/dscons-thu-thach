<?php

namespace App\Http\Middleware;

use App\Core\Gamification\CommunityStatsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HydrateCommunityStats
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            app(CommunityStatsService::class)->syncUser($request->user());
        }

        return $next($request);
    }
}
