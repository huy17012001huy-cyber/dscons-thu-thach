<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ChallengeEnrollmentService
{
    public const AUTO_APPROVED = 'auto_approved';

    public const PENDING_PAYMENT = 'pending_payment';

    public const PENDING_REVIEW = 'pending_review';

    public const DUPLICATE = 'duplicate';

    public function request(Expedition $challenge, User $user): string
    {
        return DB::transaction(function () use ($challenge, $user): string {
            $existing = ExpeditionMember::query()
                ->where('expedition_id', $challenge->id)
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
                'user_id' => $user->id,
                'class_at_join' => $user->class,
                'joined_at' => now(),
                'status' => $status,
                'approved_at' => $autoApprove ? now() : null,
                'payment_amount' => $needsPayment ? $price : null,
            ]);

            return $autoApprove
                ? self::AUTO_APPROVED
                : ($needsPayment ? self::PENDING_PAYMENT : self::PENDING_REVIEW);
        });
    }

    public function cancel(Expedition $challenge, User $user): bool
    {
        return DB::transaction(function () use ($challenge, $user): bool {
            $member = ExpeditionMember::query()
                ->where('expedition_id', $challenge->id)
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
        return DB::transaction(function () use ($challenge, $user): bool {
            $member = ExpeditionMember::query()
                ->where('expedition_id', $challenge->id)
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
}
