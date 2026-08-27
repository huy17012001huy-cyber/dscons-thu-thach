<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Brand extends Model
{
    protected $fillable = [
        'name', 'slug', 'domain', 'logo_path', 'banner_path', 'tagline', 'description', 'guide_content', 'rules_content', 'owner_id', 'status', 'verified_at',
        'theme_primary', 'theme_accent', 'theme_bg',
        'has_expeditions', 'has_academy', 'has_marketplace', 'has_qa', 'has_cv', 'has_recruitment',
        'is_invite_only', 'registration_mode',
    ];

    protected $casts = [
        'has_expeditions' => 'boolean',
        'has_academy' => 'boolean',
        'has_marketplace' => 'boolean',
        'has_qa' => 'boolean',
        'has_cv' => 'boolean',
        'has_recruitment' => 'boolean',
        'is_invite_only' => 'boolean',
        'verified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $brand): void {
            foreach (array_unique(array_filter([$brand->domain, $brand->getOriginal('domain')])) as $domain) {
                Cache::forget('brand:domain:'.strtolower($domain));
            }
        });

        static::deleted(function (self $brand): void {
            if ($brand->domain) {
                Cache::forget('brand:domain:'.strtolower($brand->domain));
            }
        });
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    /** @return HasMany<Membership, $this> */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /** @return HasMany<MembershipPlan, $this> */
    public function membershipPlans(): HasMany
    {
        return $this->hasMany(MembershipPlan::class);
    }

    /** @return HasMany<CommunityUserStat, $this> */
    public function stats(): HasMany
    {
        return $this->hasMany(CommunityUserStat::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'active';
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public static function findByDomain(string $domain): ?self
    {
        if (app()->environment('testing')) {
            return static::where('domain', $domain)->first();
        }

        return Cache::remember("brand:domain:{$domain}", 60, function () use ($domain) {
            return static::where('domain', $domain)->first();
        });
    }

    /** @return array<string, string|null> */
    public function getThemeVarsAttribute(): array
    {
        return [
            '--green' => $this->theme_primary,
            '--accent' => $this->theme_accent,
            '--bg-app' => $this->theme_bg,
        ];
    }

    /** @return array<string, mixed> */
    public function classProfiles(): array
    {
        return config('communities.classes.'.($this->slug ?: 'default'))
            ?: config('communities.classes.default', []);
    }

    /** @return array<string, mixed> */
    public function stageLabels(): array
    {
        return config('communities.stages.'.($this->slug ?: 'default'))
            ?: config('communities.stages.default', []);
    }

    /** @return array<string, mixed> */
    public function pillarProfiles(): array
    {
        return config('communities.pillars.'.($this->slug ?: 'default'))
            ?: config('communities.pillars.default', []);
    }

    public function pillarLabel(?string $pillar): string
    {
        return $this->pillarProfiles()[$pillar]['name'] ?? (string) $pillar;
    }

    /** @return HasMany<CommunitySubject, $this> */
    public function subjects(): HasMany
    {
        return $this->hasMany(CommunitySubject::class)->active();
    }

    /** @return HasMany<CommunityPostType, $this> */
    public function postTypes(): HasMany
    {
        return $this->hasMany(CommunityPostType::class)->active();
    }
}
