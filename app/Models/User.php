<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'google_id', 'username', 'password', 'avatar', 'bio', 'location', 'account_type',
        'class', 'level', 'xp', 'aip', 'streak', 'last_active_at',
        'referred_by', 'class_changed_at', 'source',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_active_at'    => 'datetime',
            'class_changed_at'  => 'datetime',
            'is_admin'          => 'boolean',
            'is_moderator'      => 'boolean',
            'password'          => 'hashed',
        ];
    }

    /** Tài khoản tạo qua webhook (funnel) — được đóng dấu nguồn ở cột source. */
    public function isWebhookCreated(): bool
    {
        return filled($this->source);
    }

    // ─── Relationships ───────────────────────────────────────────────

    public function membership(): HasOne
    {
        return $this->hasOne(Membership::class)->latestOfMany();
    }

    public function communityStats(): HasMany
    {
        return $this->hasMany(CommunityUserStat::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function xpTransactions(): HasMany
    {
        return $this->hasMany(XpTransaction::class);
    }

    public function aipTransactions(): HasMany
    {
        return $this->hasMany(AipTransaction::class);
    }

    public function daKhongCuc(): HasOne
    {
        return $this->hasOne(DaKhongCuc::class);
    }

    public function powerSymbols(): HasMany
    {
        return $this->hasMany(PowerSymbol::class);
    }

    public function expeditionMembers(): HasMany
    {
        return $this->hasMany(ExpeditionMember::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function affiliateEarnings(): HasMany
    {
        return $this->hasMany(AffiliateEarning::class, 'referrer_id');
    }

    public function recruiterProfile(): HasOne
    {
        return $this->hasOne(RecruiterProfile::class);
    }

    public function engineerProfile(): HasOne
    {
        return $this->hasOne(EngineerProfile::class);
    }

    public function engineerCv(): HasOne
    {
        return $this->hasOne(EngineerCv::class, 'user_id');
    }

    public function isRecruiter(): bool
    {
        return ! $this->is_admin && $this->account_type === 'recruiter';
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function isEngineer(): bool
    {
        return ! $this->is_admin && $this->account_type !== 'recruiter';
    }

    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    public function brandRoles(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class)->withPivot('role')->withTimestamps();
    }

    public function communityPreferences(): HasMany
    {
        return $this->hasMany(UserCommunityPreference::class);
    }

    public function billingProfiles(): HasMany
    {
        return $this->hasMany(UserBillingProfile::class);
    }

    public function isBrandAdmin(?int $brandId = null): bool
    {
        $brandId ??= app()->bound('brand') ? brand()->id : null;
        return $this->isCommunityAdmin($brandId);
    }

    public function communityRole(?int $brandId = null): ?string
    {
        $brandId ??= app()->bound('brand') ? brand()->id : null;
        if (! $brandId) {
            return null;
        }

        return $this->brandRoles()
            ->where('brand_id', $brandId)
            ->first()?->pivot?->role;
    }

    public function hasCommunityRole(string|array $roles, ?int $brandId = null): bool
    {
        $role = $this->communityRole($brandId);
        return $role !== null && in_array($role, (array) $roles, true);
    }

    public function isCommunityAdmin(?int $brandId = null): bool
    {
        return $this->isSuperAdmin() || $this->hasCommunityRole(['owner', 'admin'], $brandId);
    }

    public function isCommunityModerator(?int $brandId = null): bool
    {
        return $this->isSuperAdmin() || $this->hasCommunityRole(['owner', 'admin', 'moderator'], $brandId);
    }

    public function isCommunityOwner(?int $brandId = null): bool
    {
        $brandId ??= app()->bound('brand') ? brand()->id : null;
        if (!$brandId) return false;

        return $this->isSuperAdmin() || $this->brandRoles()
            ->where('brand_id', $brandId)
            ->where('role', 'owner')
            ->exists();
    }

    // ─── Computed Attributes ─────────────────────────────────────────

    public function getJobStageAttribute(): string
    {
        $brand = app()->bound('brand')
            ? brand()
            : new \App\Models\Brand(['slug' => 'default']);
        $stages = app(\App\Support\CommunityBrandSettings::class)->stageLabels($brand);

        return $stages[$this->levelBadgeTone()] ?? 'Người mới vào nghề';
    }

    public function getClassLabelAttribute(): string
    {
        if ($this->level < 10) return 'Beginner';
        return $this->communityClassProfile()['name'] ?? 'Beginner';
    }

    public function getClassColorAttribute(): string
    {
        if ($this->level < 10) return 'gray';
        return $this->communityClassProfile()['color_token'] ?? 'blue';
    }

    public function getClassEmojiAttribute(): string
    {
        return $this->level < 10 ? '•' : '•';
    }

    public function getClassIconAttribute(): string
    {
        return $this->communityClassProfile()['icon'] ?? 'layers';
    }

    public function levelBadgeTone(): string
    {
        return match (true) {
            $this->level <= 10 => 'newcomer',
            $this->level <= 30 => 'practitioner',
            $this->level <= 60 => 'core',
            $this->level <= 100 => 'expert',
            default => 'mentor',
        };
    }

    public function levelBadgeColor(): string
    {
        $brand = app()->bound('brand')
            ? brand()
            : new \App\Models\Brand(['slug' => 'default']);

        return app(\App\Support\CommunityBrandSettings::class)->badgeColors($brand)[$this->levelBadgeTone()]
            ?? '#1F77BE';
    }

    public function communityClassProfile(): array
    {
        $profiles = app()->bound('brand')
            ? brand()->classProfiles()
            : config('communities.classes.default', []);

        $profile = $profiles[$this->class] ?? null;
        if (!$profile) {
            return [];
        }

        $profile['color_token'] ??= app()->bound('brand') && brand()->slug === 'dscons'
            ? 'blue'
            : match ($this->class) {
                'offer_architect' => 'amber',
                'traffic_mage' => 'purple',
                'conversion_ranger' => 'emerald',
                'delivery_assassin' => 'blue',
                'continuity_captain' => 'red',
                default => 'gray',
            };
        return $profile;
    }

    public function getDaCountAttribute(): int
    {
        return $this->daKhongCuc?->total_count ?? 0;
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
                return $this->avatar;
            }

            return asset('storage/' . $this->avatar);
        }
        $initials = collect(explode(' ', $this->name))
            ->map(fn($w) => strtoupper(substr($w, 0, 1)))
            ->take(2)
            ->join('');
        return 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&background=DCECF7&color=125A96&bold=true&size=80';
    }

    public function isActive(): bool
    {
        $m = $this->membership;
        return $m && in_array($m->status, ['trial', 'active']);
    }

    public function hasPremiumMembership(?int $brandId = null): bool
    {
        $brandId ??= app()->bound('brand') ? brand()->id : null;
        if (!$brandId) return false;

        return $this->memberships()->withoutGlobalScopes()
            ->where('brand_id', $brandId)
            ->where('tier', 'premium')
            ->whereIn('status', ['active', 'trial'])
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->exists();
    }

    /** Community participation is tracked by the brand_user pivot. */
    public function isCommunityParticipant(?int $brandId = null): bool
    {
        $brandId ??= app()->bound('brand') ? brand()->id : null;
        if (!$brandId) return false;
        if ($this->is_admin) return true;

        $hasCommunityRole = $this->brandRoles()
            ->where('brand_id', $brandId)
            ->whereIn('role', ['member', 'moderator', 'admin', 'owner'])
            ->exists();

        return $hasCommunityRole;
    }
}
