<?php

declare(strict_types=1);

namespace Modules\Community\Application;

use App\Models\Brand;
use App\Models\CommunityApplication;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class CommunityApplicationService
{
    /** @param array<string, mixed> $attributes */
    public function submit(User $applicant, array $attributes): CommunityApplication
    {
        return DB::transaction(fn (): CommunityApplication => CommunityApplication::create([
            ...$attributes,
            'applicant_id' => $applicant->id,
            'status' => 'pending',
        ]));
    }

    public function approve(int $applicationId, User $actor, ?string $reviewNote): ?Brand
    {
        $this->assertSuperAdmin($actor);

        return DB::transaction(function () use ($applicationId, $actor, $reviewNote): ?Brand {
            $application = CommunityApplication::query()->lockForUpdate()->findOrFail($applicationId);
            if ($application->status !== 'pending') {
                return null;
            }

            $brand = Brand::create([
                'name' => $application->name,
                'slug' => $application->slug,
                'domain' => $application->slug.'.local',
                'logo_path' => $application->logo_path,
                'banner_path' => $application->banner_path,
                'tagline' => $application->tagline,
                'description' => $application->description,
                'owner_id' => $application->applicant_id,
                'status' => 'active',
                'verified_at' => now(),
                'theme_primary' => '#1F77BE',
                'theme_accent' => '#E1F4F7',
                'theme_bg' => '#F7FAFC',
            ]);
            $this->createOwnerAccess($brand, $application->applicant_id);
            $this->createPlans($brand, $application);
            $application->update([
                'status' => 'approved',
                'reviewed_by' => $actor->id,
                'review_note' => $reviewNote,
            ]);

            return $brand;
        });
    }

    public function reject(int $applicationId, User $actor, ?string $reviewNote): bool
    {
        $this->assertSuperAdmin($actor);

        return DB::transaction(function () use ($applicationId, $actor, $reviewNote): bool {
            $application = CommunityApplication::query()->lockForUpdate()->findOrFail($applicationId);
            if ($application->status !== 'pending') {
                return false;
            }

            $application->update([
                'status' => 'rejected',
                'reviewed_by' => $actor->id,
                'review_note' => $reviewNote,
            ]);

            return true;
        });
    }

    private function createOwnerAccess(Brand $brand, int $ownerId): void
    {
        DB::table('brand_user')->insert([
            'brand_id' => $brand->id,
            'user_id' => $ownerId,
            'role' => 'owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Membership::withoutGlobalScopes()->create([
            'brand_id' => $brand->id,
            'user_id' => $ownerId,
            'tier' => 'free',
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addYears(10),
        ]);
    }

    private function createPlans(Brand $brand, CommunityApplication $application): void
    {
        foreach ([
            ['tier' => 'free', 'name' => 'Free', 'price' => 0, 'benefits' => ['Nội dung công khai', 'Feed cộng đồng'], 'status' => 'published'],
            ['tier' => 'premium', 'name' => 'Premium', 'price' => $application->proposed_premium_price ?? 0, 'benefits' => ['Toàn bộ khóa học', 'Challenge và sự kiện premium'], 'status' => 'pending_review', 'sepay_account' => $application->proposed_sepay_account, 'sepay_bank' => $application->proposed_sepay_bank],
        ] as $attributes) {
            MembershipPlan::withoutGlobalScopes()->create([...$attributes, 'brand_id' => $brand->id]);
        }
    }

    private function assertSuperAdmin(User $actor): void
    {
        if (! $actor->isSuperAdmin()) {
            throw new AuthorizationException('Super admin access is required.');
        }
    }
}
