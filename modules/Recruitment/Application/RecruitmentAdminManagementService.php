<?php

declare(strict_types=1);

namespace Modules\Recruitment\Application;

use App\Core\Audit\AuditLogger;
use App\Core\CommunityContext;
use App\Models\RecruiterPlan;
use App\Models\RecruiterProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class RecruitmentAdminManagementService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly CommunityContext $context,
    ) {}

    public function approveRecruiter(int $profileId, User $actor, bool $global): ?RecruiterProfile
    {
        return $this->updateRecruiter($profileId, $actor, $global, [
            'verification_status' => 'verified',
            'verified_at' => now(),
            'reviewed_by' => $actor->id,
            'review_note' => null,
        ]);
    }

    public function rejectRecruiter(int $profileId, User $actor, bool $global): ?RecruiterProfile
    {
        return $this->updateRecruiter($profileId, $actor, $global, [
            'verification_status' => 'rejected',
            'reviewed_by' => $actor->id,
        ]);
    }

    /** @param array{name:string,description:?string,contact_credits:int,duration_days:?int,price:int} $attributes */
    public function createPlan(User $actor, bool $global, array $attributes): ?RecruiterPlan
    {
        return DB::transaction(function () use ($actor, $global, $attributes): ?RecruiterPlan {
            $brandId = $this->managedBrandId($actor, $global);
            if ($brandId === null) {
                return null;
            }

            $plan = RecruiterPlan::withoutGlobalScopes()->create([
                ...$attributes,
                'brand_id' => $brandId,
                'is_active' => true,
            ]);
            DB::afterCommit(fn () => $this->audit->record('recruitment', 'recruiter_plan_created', $actor, $plan, $brandId));

            return $plan;
        });
    }

    public function togglePlan(int $planId, User $actor, bool $global): ?RecruiterPlan
    {
        return DB::transaction(function () use ($planId, $actor, $global): ?RecruiterPlan {
            $plan = $this->managedPlan($planId, $actor, $global);
            if (! $plan) {
                return null;
            }

            $isActive = ! $plan->is_active;
            $plan->update(['is_active' => $isActive]);
            DB::afterCommit(fn () => $this->audit->record(
                'recruitment',
                'recruiter_plan_toggled',
                $actor,
                $plan,
                $plan->brand_id,
                ['is_active' => $isActive],
            ));

            return $plan->refresh();
        });
    }

    /** @param array<string, mixed> $attributes */
    private function updateRecruiter(int $profileId, User $actor, bool $global, array $attributes): ?RecruiterProfile
    {
        return DB::transaction(function () use ($profileId, $actor, $global, $attributes): ?RecruiterProfile {
            $profile = $this->managedRecruiter($profileId, $actor, $global);
            if (! $profile) {
                return null;
            }

            $profile->update($attributes);
            DB::afterCommit(fn () => $this->audit->record(
                'recruitment',
                'recruiter_'.(string) $profile->verification_status,
                $actor,
                $profile,
                $profile->brand_id,
            ));

            return $profile->refresh();
        });
    }

    private function managedRecruiter(int $profileId, User $actor, bool $global): ?RecruiterProfile
    {
        $query = RecruiterProfile::withoutGlobalScopes()->whereKey($profileId);
        $brandId = $this->managedBrandId($actor, $global);
        if ($brandId === null) {
            return null;
        }
        if (! $global) {
            $query->where('brand_id', $brandId);
        }

        return $query->lockForUpdate()->firstOrFail();
    }

    private function managedPlan(int $planId, User $actor, bool $global): ?RecruiterPlan
    {
        $query = RecruiterPlan::withoutGlobalScopes()->whereKey($planId);
        $brandId = $this->managedBrandId($actor, $global);
        if ($brandId === null) {
            return null;
        }
        if (! $global) {
            $query->where('brand_id', $brandId);
        }

        return $query->lockForUpdate()->firstOrFail();
    }

    private function managedBrandId(User $actor, bool $global): ?int
    {
        if ($global) {
            return $actor->isSuperAdmin() ? $this->context->require()->id : null;
        }

        $brand = $this->context->require();

        return $actor->isCommunityAdmin($brand->id) ? $brand->id : null;
    }
}
