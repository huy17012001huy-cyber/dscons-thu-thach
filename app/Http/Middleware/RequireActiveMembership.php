<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireActiveMembership
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        // This middleware protects community participation, not Premium
        // learning access. Premium is checked by ChallengeDetail/AcademyDetail.
        if ($user->is_admin) {
            return $next($request);
        }

        // Only redirect to onboarding if Lv10+ and no class
        if ($user->level >= 10 && empty($user->class)) {
            return redirect()->route('onboarding');
        }

        if (! $user->isCommunityParticipant()) {
            $communitySlug = app()->bound('brand') ? brand()->slug : null;

            return $communitySlug
                ? redirect()->route('community.preview', ['community' => $communitySlug])
                    ->with('error', 'Hãy tham gia cộng đồng để đăng bài và tương tác.')
                : redirect()->route('discovery')
                    ->with('error', 'Hãy tham gia cộng đồng để đăng bài và tương tác.');
        }

        return $next($request);
    }
}
