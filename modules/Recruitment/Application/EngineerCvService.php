<?php

declare(strict_types=1);

namespace Modules\Recruitment\Application;

use App\Core\CommunityContext;
use App\Models\Brand;
use App\Models\EngineerCv;
use App\Models\EngineerProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class EngineerCvService
{
    public function __construct(private readonly CommunityContext $context) {}

    /** @return array{profile: EngineerProfile, cv: EngineerCv} */
    public function ensureWorkspace(User $engineer): array
    {
        return DB::transaction(function () use ($engineer): array {
            $brand = $this->availableBrand($engineer);
            $profile = EngineerProfile::query()
                ->where('brand_id', $brand->id)
                ->where('user_id', $engineer->id)
                ->lockForUpdate()
                ->first();
            if (! $profile) {
                $profile = EngineerProfile::create([
                    'brand_id' => $brand->id,
                    'user_id' => $engineer->id,
                    'anonymized_code' => $this->anonymizedCode($engineer),
                    'contact_email' => $engineer->email,
                ]);
            }

            $cv = EngineerCv::query()
                ->where('brand_id', $brand->id)
                ->where('user_id', $engineer->id)
                ->lockForUpdate()
                ->first();
            if (! $cv) {
                $cv = EngineerCv::create([
                    'brand_id' => $brand->id,
                    'user_id' => $engineer->id,
                    'data' => [],
                ]);
            }

            return ['profile' => $profile, 'cv' => $cv];
        });
    }

    /**
     * @param  array<string, mixed>  $profileAttributes
     * @param  array<string, mixed>  $cvAttributes
     */
    public function save(User $engineer, bool $publish, array $profileAttributes, array $cvAttributes): EngineerCv
    {
        return DB::transaction(function () use ($engineer, $publish, $profileAttributes, $cvAttributes): EngineerCv {
            $workspace = $this->ensureWorkspace($engineer);
            $profile = $workspace['profile'];
            $cv = $workspace['cv'];

            $profile->update([
                ...$profileAttributes,
                'anonymized_code' => $profile->anonymized_code ?: $this->anonymizedCode($engineer),
                'contact_email' => $engineer->email,
                'is_searchable' => $publish,
            ]);
            $cv->update([
                ...$cvAttributes,
                'status' => $publish ? 'published' : 'draft',
                'published_at' => $publish ? now() : null,
            ]);

            return $cv->refresh();
        });
    }

    private function availableBrand(User $engineer): Brand
    {
        $brand = $this->context->require();
        abort_unless($engineer->isEngineer(), 403);
        abort_unless($brand->has_cv || (app()->environment('testing') && $brand->slug === 'dscons'), 404);

        return $brand;
    }

    private function anonymizedCode(User $engineer): string
    {
        return 'KYS-'.str_pad((string) $engineer->id, 5, '0', STR_PAD_LEFT);
    }
}
