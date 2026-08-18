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
        'name', 'email', 'username', 'password', 'avatar', 'bio',
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

    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    public function brandRoles(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class)->withPivot('role')->withTimestamps();
    }

    public function isBrandAdmin(?int $brandId = null): bool
    {
        $brandId ??= app()->bound('brand') ? brand()->id : null;
        if (!$brandId) return $this->is_admin;
        return $this->is_admin || $this->brandRoles()
            ->where('brand_id', $brandId)
            ->where('role', 'admin')
            ->exists();
    }

    // ─── Computed Attributes ─────────────────────────────────────────

    public function getJobStageAttribute(): string
    {
        return match(true) {
            $this->level <= 10  => 'Tân binh',
            $this->level <= 30  => 'Freelancer',
            $this->level <= 60  => 'Growing',
            $this->level <= 100 => 'Chuyên gia',
            $this->level <= 200 => 'Business Owner',
            default             => 'Empire Builder',
        };
    }

    public function getClassLabelAttribute(): string
    {
        if ($this->level < 10) return 'Beginner';
        return match($this->class) {
            'offer_architect'    => 'Offer Architect',
            'traffic_mage'       => 'Traffic Mage',
            'conversion_ranger'  => 'Conversion Ranger',
            'delivery_assassin'  => 'Delivery Assassin',
            'continuity_captain' => 'Continuity Captain',
            default              => 'Beginner',
        };
    }

    public function getClassColorAttribute(): string
    {
        if ($this->level < 10) return 'gray';
        return match($this->class) {
            'offer_architect'    => 'amber',
            'traffic_mage'       => 'purple',
            'conversion_ranger'  => 'emerald',
            'delivery_assassin'  => 'blue',
            'continuity_captain' => 'red',
            default              => 'gray',
        };
    }

    public function getClassEmojiAttribute(): string
    {
        if ($this->level < 10) return '🐔';
        return match($this->class) {
            'offer_architect'    => '🔥',
            'traffic_mage'       => '✨',
            'conversion_ranger'  => '🎯',
            'delivery_assassin'  => '⚙️',
            'continuity_captain' => '🔗',
            default              => '🐔',
        };
    }

    public function getDaCountAttribute(): int
    {
        return $this->daKhongCuc?->total_count ?? 0;
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        $initials = collect(explode(' ', $this->name))
            ->map(fn($w) => strtoupper(substr($w, 0, 1)))
            ->take(2)
            ->join('');
        return 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&background=EDE9FE&color=4C1D95&bold=true&size=80';
    }

    public function isActive(): bool
    {
        $m = $this->membership;
        return $m && in_array($m->status, ['trial', 'active']);
    }
}
