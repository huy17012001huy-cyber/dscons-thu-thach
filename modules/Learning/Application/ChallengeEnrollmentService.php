<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Core\CommunityContext;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Learning\Domain\Events\ChallengeEnrollmentRequested;
use Modules\Learning\Domain\Events\ChallengeEnrollmentReviewed;

final class ChallengeEnrollmentService
{
    public const AUTO_APPROVED = 'auto_approved';

    public const PENDING_PAYMENT = 'pending_payment';

    public const PENDING_REVIEW = 'pending_review';

    public const DUPLICATE = 'duplicate';

    public function __construct(private readonly CommunityContext $context) {}

    public function request(Expedition $challenge, User $user): string
    {
        $this->assertCurrentCommunity($challenge);

        return DB::transaction(function () use ($challenge, $user): string {
            $existing = ExpeditionMember::query()
                ->where('expedition_id', $challenge->id)
                ->where('brand_id', $challenge->brand_id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if ($existing->status === 'rejected' || $existing->kicked_at) {
                    $existing->delete();
                } else {
                    return self::DUPLICATE;
                }
            }

            $autoApprove = $user->isWebhookCreated() || $user->hasPremiumMembership($challenge->brand_id);
            $price = (int) $challenge->price;
            $needsPayment = ! $autoApprove && $price > 0;
            $status = $autoApprove ? 'approved' : ($needsPayment ? 'pending_payment' : 'pending');

            ExpeditionMember::create([
                'expedition_id' => $challenge->id,
                'brand_id' => $challenge->brand_id,
                'user_id' => $user->id,
                'class_at_join' => $user->class,
                'joined_at' => now(),
                'status' => $status,
                'approved_at' => $autoApprove ? now() : null,
                'payment_amount' => $needsPayment ? $price : null,
            ]);

            $outcome = $autoApprove
                ? self::AUTO_APPROVED
                : ($needsPayment ? self::PENDING_PAYMENT : self::PENDING_REVIEW);
            if ($outcome === self::PENDING_REVIEW) {
                DB::afterCommit(fn () => Event::dispatch(new ChallengeEnrollmentRequested($challenge, $user)));
            }

            return $outcome;
        });
    }

    public function cancel(Expedition $challenge, User $user): bool
    {
        $this->assertCurrentCommunity($challenge);

        return DB::transaction(function () use ($challenge, $user): bool {
            $member = ExpeditionMember::query()
                ->where('expedition_id', $challenge->id)
                ->where('brand_id', $challenge->brand_id)
                ->where('user_id', $user->id)
                ->whereIn('status', ['pending', 'pending_payment'])
                ->lockForUpdate()
                ->first();

            if (! $member) {
                return false;
            }

            $member->delete();

            return true;
        });
    }

    public function start(Expedition $challenge, User $user): bool
    {
        $this->assertCurrentCommunity($challenge);

        return DB::transaction(function () use ($challenge, $user): bool {
            $member = ExpeditionMember::query()
                ->where('expedition_id', $challenge->id)
                ->where('brand_id', $challenge->brand_id)
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereNull('personal_starts_at')
                ->lockForUpdate()
                ->first();

            if (! $member) {
                return false;
            }

            $member->update(['personal_starts_at' => now()]);

            return true;
        });
    }

    public function approve(Expedition $challenge, int $memberId, User $approver): ?ExpeditionMember
    {
        return $this->transitionPendingEnrollment($challenge, $memberId, $approver, [
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approver->id,
        ]);
    }

    public function reject(Expedition $challenge, int $memberId, User $approver): ?ExpeditionMember
    {
        return $this->transitionPendingEnrollment($challenge, $memberId, $approver, ['status' => 'rejected']);
    }

    /** @param array<string, mixed> $attributes */
    private function transitionPendingEnrollment(
        Expedition $challenge,
        int $memberId,
        User $approver,
        array $attributes,
    ): ?ExpeditionMember {
        $this->assertCurrentCommunity($challenge);

        if (! $approver->isCommunityAdmin($challenge->brand_id)) {
            return null;
        }

        return DB::transaction(function () use ($challenge, $memberId, $attributes): ExpeditionMember {
            $member = ExpeditionMember::query()
                ->whereKey($memberId)
                ->where('expedition_id', $challenge->id)
                ->where('brand_id', $challenge->brand_id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->firstOrFail();

            $member->update($attributes);
            DB::afterCommit(fn () => Event::dispatch(new ChallengeEnrollmentReviewed($challenge->id, $member->user_id, (string) $attributes['status'])));

            return $member->load('user');
        });
    }

    private function assertCurrentCommunity(Expedition $challenge): void
    {
        $brand = $this->context->current();

        if ($brand && $challenge->brand_id !== $brand->id) {
            throw new AuthorizationException('Challenge does not belong to the current community.');
        }
    }
}
