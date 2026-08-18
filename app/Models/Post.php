<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\HasBrand;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, SoftDeletes, HasBrand;

    protected $fillable = [
        'user_id', 'title', 'content', 'pillar', 'topic_id', 'is_cot', 'cot_at', 'cot_by',
        'is_pinned', 'is_signal', 'rune_active', 'rune_expires_at',
        'rune_first_comment_user_id', 'view_count', 'brand_id',
    ];

    protected $casts = [
        'is_cot'          => 'boolean',
        'is_pinned'       => 'boolean',
        'is_signal'       => 'boolean',
        'rune_active'     => 'boolean',
        'cot_at'          => 'datetime',
        'rune_expires_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function comments(): HasMany { return $this->hasMany(Comment::class)->whereNull('parent_id'); }
    public function allComments(): HasMany { return $this->hasMany(Comment::class); }
    public function likes(): MorphMany { return $this->morphMany(Like::class, 'likeable'); }
    public function bookmarks(): HasMany { return $this->hasMany(Bookmark::class); }
    public function attachments(): HasMany { return $this->hasMany(PostAttachment::class); }
    public function images(): HasMany { return $this->hasMany(PostImage::class)->orderBy('order_index'); }
    public function cotBy(): BelongsTo { return $this->belongsTo(User::class, 'cot_by'); }
    public function topic(): BelongsTo { return $this->belongsTo(Topic::class); }

    public function getPillarLabelAttribute(): string {
        return match($this->pillar) {
            'offer' => 'Offer', 'traffic' => 'Thu hút', 'conversion' => 'Chuyển đổi',
            'delivery' => 'Cung ứng', 'continuity' => 'Continuity', default => $this->pillar,
        };
    }

    public function getPillarColorAttribute(): string {
        return match($this->pillar) {
            'offer' => 'amber', 'traffic' => 'purple', 'conversion' => 'emerald',
            'delivery' => 'blue', 'continuity' => 'red', default => 'gray',
        };
    }

    public function scopeCot($q) { return $q->where('is_cot', true); }
    public function scopeSignal($q) { return $q->where('is_signal', true); }
    public function scopeByPillar($q, string $p) { return $q->where('pillar', $p); }

    public function isRuneActive(): bool {
        return $this->rune_active && $this->rune_expires_at?->isFuture();
    }

    public function isLikedBy(?User $user): bool {
        if (!$user) return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function isBookmarkedBy(?User $user): bool {
        if (!$user) return false;
        return Bookmark::where('user_id', $user->id)->where('post_id', $this->id)->exists();
    }
}
