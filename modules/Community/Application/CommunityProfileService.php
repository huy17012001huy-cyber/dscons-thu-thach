<?php

declare(strict_types=1);

namespace Modules\Community\Application;

use App\Core\CommunityContext;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class CommunityProfileService
{
    public function __construct(private readonly CommunityContext $context) {}

    public function update(Brand $community, User $actor, CommunityProfileData $data): Brand
    {
        $brand = $this->context->require();
        if (! $community->is($brand) || ! $actor->isCommunityAdmin($brand->id)) {
            throw new AuthorizationException('Community admin access is required.');
        }

        return DB::transaction(function () use ($community, $data): Brand {
            $community->update([
                'name' => $data->name,
                'tagline' => $data->tagline,
                'description' => $data->description,
                'guide_content' => $data->guideContent,
                'rules_content' => $data->rulesContent,
                'logo_path' => $data->logoPath,
                'banner_path' => $data->bannerPath,
            ]);

            return $community->fresh() ?? $community;
        });
    }
}
