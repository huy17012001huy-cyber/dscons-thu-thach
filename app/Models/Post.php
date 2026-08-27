<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $brand_id
 * @property int $user_id
 * @property string|null $title
 * @property string $content
 * @property string|null $pillar
 * @property int $likes_count
 * @property bool $likes_exists
 * @property bool $bookmarks_exists
 * @property-read User|null $user
 */
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasBrand, HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'brand_id', 'title', 'slug', 'content', 'content_html', 'content_format',
        'pillar', 'topic_id', 'subject_id', 'post_type_id', 'is_cot', 'cot_at', 'cot_by',
        'is_pinned', 'is_signal', 'rune_active', 'rune_expires_at',
        'rune_first_comment_user_id', 'view_count',
    ];

    protected $casts = [
        'is_cot' => 'boolean',
        'is_pinned' => 'boolean',
        'is_signal' => 'boolean',
        'rune_active' => 'boolean',
        'cot_at' => 'datetime',
        'rune_expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (self $post): void {
            if (filled($post->slug)) {
                return;
            }

            $base = Str::slug($post->title ?: Str::limit(strip_tags($post->content), 60, '')) ?: 'bai-viet';
            $post->forceFill(['slug' => $base.'-'.$post->id])->saveQuietly();
        });
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Comment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    /** @return HasMany<Comment, $this> */
    public function allComments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** @return MorphMany<Like, $this> */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /** @return HasMany<Bookmark, $this> */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /** @return HasMany<PostAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(PostAttachment::class);
    }

    /** @return HasMany<PostImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class)->orderBy('order_index');
    }

    /** @return BelongsTo<User, $this> */
    public function cotBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cot_by');
    }

    /** @return BelongsTo<Topic, $this> */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /** @return BelongsTo<CommunitySubject, $this> */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(CommunitySubject::class, 'subject_id');
    }

    /** @return BelongsTo<CommunityPostType, $this> */
    public function postType(): BelongsTo
    {
        return $this->belongsTo(CommunityPostType::class, 'post_type_id');
    }

    public function getPillarLabelAttribute(): string
    {
        return app()->bound('brand') ? brand()->pillarLabel($this->pillar) : (string) $this->pillar;
    }

    public function getPillarColorAttribute(): string
    {
        return match ($this->pillar) {
            'offer' => 'amber', 'traffic' => 'purple', 'conversion' => 'emerald',
            'delivery' => 'blue', 'continuity' => 'red', default => 'gray',
        };
    }

    /**
     * @param  Builder<Post>  $query
     *
     * @phpstan-return Builder<Post>
     */
    public function scopeCot(Builder $query): Builder
    {
        return $query->where('is_cot', true);
    }

    /**
     * @param  Builder<Post>  $query
     *
     * @phpstan-return Builder<Post>
     */
    public function scopeSignal(Builder $query): Builder
    {
        return $query->where('is_signal', true);
    }

    /**
     * @param  Builder<Post>  $query
     *
     * @phpstan-return Builder<Post>
     */
    public function scopeByPillar(Builder $query, string $p): Builder
    {
        return $query->where('pillar', $p);
    }

    public function isRuneActive(): bool
    {
        return $this->rune_active && $this->rune_expires_at?->isFuture();
    }

    public function isLikedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function isBookmarkedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return Bookmark::where('user_id', $user->id)->where('post_id', $this->id)->exists();
    }
}
