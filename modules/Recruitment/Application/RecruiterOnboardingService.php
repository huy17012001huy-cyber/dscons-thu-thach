<?php

declare(strict_types=1);

namespace Modules\Recruitment\Application;

use App\Core\CommunityContext;
use App\Models\Brand;
use App\Models\RecruiterProfile;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RecruiterOnboardingService
{
    public function __construct(private readonly CommunityContext $context) {}

    /** @param array{companyName:string,businessEmail:?string,website:?string,industry:string,description:?string} $data */
    public function save(Brand $community, User $user, array $data): RecruiterProfile
    {
        if (! $this->context->current()?->is($community)) {
            throw new AuthorizationException('Recruiter onboarding must use the current community.');
        }

        return DB::transaction(function () use ($community, $user, $data): RecruiterProfile {
            $user->update(['account_type' => 'recruiter']);

            return RecruiterProfile::updateOrCreate(
                ['brand_id' => $community->id, 'user_id' => $user->id],
                [
                    'company_name' => $data['companyName'],
                    'company_slug' => Str::slug($data['companyName']).'-'.$user->id,
                    'business_email' => $data['businessEmail'] ?: $user->email,
                    'website' => $data['website'] ?: null,
                    'industry' => $data['industry'],
                    'description' => $data['description'] ?: null,
                    'verification_status' => 'pending',
                ],
            );
        });
    }
}
