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
        'name', 'slug', 'domain', 'logo_path', 'banner_path', 'tagline', 'description', 'owner_id', 'status', 'verified_at',
        'theme_primary', 'theme_accent', 'theme_bg',
        'has_expeditions', 'has_academy', 'has_marketplace', 'has_qa',
        'is_invite_only', 'registration_mode',
    ];

    protected $casts = [
        'has_expeditions' => 'boolean',
        'has_academy'     => 'boolean',
        'has_marketplace' => 'boolean',
        'has_qa'          => 'boolean',
        'is_invite_only'  => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function membershipPlans(): HasMany
    {
        return $this->hasMany(MembershipPlan::class);
    }

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

    public function getThemeVarsAttribute(): array
    {
        return [
            '--green'  => $this->theme_primary,
            '--accent' => $this->theme_accent,
            '--bg-app' => $this->theme_bg,
        ];
    }
}
