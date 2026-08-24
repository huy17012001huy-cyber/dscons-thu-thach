<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifiedRecruiter
{
    public function handle(Request $request, Closure $next): Response
    {
        $profile = $request->user()?->recruiterProfile;
        if (! $profile || ! $profile->isVerified()) {
            return redirect()->to(community_route('recruiter.onboarding'))
                ->with('status', 'Hồ sơ doanh nghiệp đang chờ xác minh.');
        }

        return $next($request);
    }
}
