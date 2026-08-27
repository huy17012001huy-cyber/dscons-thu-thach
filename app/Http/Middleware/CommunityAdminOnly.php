<?php

namespace App\Http\Middleware;

use App\Models\Brand;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CommunityAdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isCommunityAdmin($this->brandId($request)), 403);

        return $next($request);
    }

    private function brandId(Request $request): ?int
    {
        $community = $request->route('community');

        return $community instanceof Brand ? $community->id : (app()->bound('brand') ? brand()->id : null);
    }
}
