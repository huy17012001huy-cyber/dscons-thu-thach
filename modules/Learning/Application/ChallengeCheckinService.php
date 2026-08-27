<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Core\CommunityContext;
use App\Core\Gamification\XpService;
use App\Models\Expedition;
use App\Models\ExpeditionCheckin;
use App\Models\ExpeditionMember;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class ChallengeCheckinService
{
    public function __construct(
        private readonly CommunityContext $context,
        private readonly XpService $xp,
    ) {}

    public function checkin(Expedition $challenge, User $user, string $content): ChallengeCheckinOutcome
    {
        $this->assertCurrentCommunity($challenge);

        $outcome = DB::transaction(function () use ($challenge, $user, $content): ChallengeCheckinOutcome {
            $member = ExpeditionMember::query()
                ->where('expedition_id', $challenge->id)
                ->where('brand_id', $challenge->brand_id)
                ->where('user_id', $user->id)
                ->whereIn('status', ['approved', 'paid'])
                ->whereNull('kicked_at')
                ->lockForUpdate()
                ->first();
            if (! $member) {
                return ChallengeCheckinOutcome::NotEnrolled;
            }
            if (ExpeditionCheckin::query()
                ->where('expedition_id', $challenge->id)
                ->where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->exists()) {
                return ChallengeCheckinOutcome::AlreadyCheckedIn;
            }

            ExpeditionCheckin::create([
                'brand_id' => $challenge->brand_id,
                'expedition_id' => $challenge->id,
                'user_id' => $user->id,
                'content' => $content,
            ]);
            $member->update(['last_checkin_at' => now(), 'consecutive_missed_days' => 0]);

            return ChallengeCheckinOutcome::CheckedIn;
        });

        if ($outcome === ChallengeCheckinOutcome::CheckedIn) {
            $this->xp->award(
                $user,
                'expedition_checkin',
                1.0,
                'Check-in Challenge: '.$challenge->title,
                $challenge,
            );
        }

        return $outcome;
    }

    private function assertCurrentCommunity(Expedition $challenge): void
    {
        $brand = $this->context->current();

        if ($brand && $challenge->brand_id !== $brand->id) {
            throw new AuthorizationException('Challenge does not belong to the current community.');
        }
    }
}
