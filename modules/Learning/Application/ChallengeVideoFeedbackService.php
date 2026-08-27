<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Core\CommunityContext;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class ChallengeVideoFeedbackService
{
    public function __construct(private readonly CommunityContext $context) {}

    public function submit(Expedition $challenge, User $user, string $url): ChallengeVideoFeedbackOutcome
    {
        $this->assertCurrentCommunity($challenge);
        if (blank($url)) {
            return ChallengeVideoFeedbackOutcome::MissingUrl;
        }

        return DB::transaction(function () use ($challenge, $user, $url): ChallengeVideoFeedbackOutcome {
            $member = $this->approvedMember($challenge, $user->id);
            if (! $member) {
                return ChallengeVideoFeedbackOutcome::NotEnrolled;
            }

            $member->update([
                'video_feedback_url' => $url,
                'video_feedback_status' => 'pending',
                'video_feedback_at' => now(),
            ]);

            return ChallengeVideoFeedbackOutcome::Submitted;
        });
    }

    public function approve(Expedition $challenge, int $memberId, User $reviewer): ?ExpeditionMember
    {
        return $this->review($challenge, $memberId, $reviewer, [
            'video_feedback_status' => 'approved',
            'video_feedback_note' => 'Video đạt yêu cầu! Ban tổ chức sẽ liên hệ bạn về phần thưởng.',
        ]);
    }

    public function reject(Expedition $challenge, int $memberId, User $reviewer, string $note): ?ExpeditionMember
    {
        return $this->review($challenge, $memberId, $reviewer, [
            'video_feedback_status' => 'rejected',
            'video_feedback_note' => $note,
            'video_feedback_url' => null,
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function review(Expedition $challenge, int $memberId, User $reviewer, array $attributes): ?ExpeditionMember
    {
        $this->assertCurrentCommunity($challenge);
        if (! $reviewer->isCommunityAdmin($challenge->brand_id)) {
            return null;
        }

        return DB::transaction(function () use ($challenge, $memberId, $attributes): ?ExpeditionMember {
            $member = ExpeditionMember::query()
                ->whereKey($memberId)
                ->where('expedition_id', $challenge->id)
                ->where('brand_id', $challenge->brand_id)
                ->where('video_feedback_status', 'pending')
                ->lockForUpdate()
                ->first();
            if (! $member) {
                return null;
            }

            $member->update($attributes);

            return $member->load('user');
        });
    }

    private function approvedMember(Expedition $challenge, int $userId): ?ExpeditionMember
    {
        return ExpeditionMember::query()
            ->where('expedition_id', $challenge->id)
            ->where('brand_id', $challenge->brand_id)
            ->where('user_id', $userId)
            ->whereIn('status', ['approved', 'paid'])
            ->whereNull('kicked_at')
            ->lockForUpdate()
            ->first();
    }

    private function assertCurrentCommunity(Expedition $challenge): void
    {
        $brand = $this->context->current();

        if ($brand && $challenge->brand_id !== $brand->id) {
            throw new AuthorizationException('Challenge does not belong to the current community.');
        }
    }
}
