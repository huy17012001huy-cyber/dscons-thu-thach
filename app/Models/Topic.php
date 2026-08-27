<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Database\Factories\TopicFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property int $brand_id */
class Topic extends Model
{
    /** @use HasFactory<TopicFactory> */
    use HasBrand, HasFactory;

    protected $fillable = ['name', 'emoji', 'slug', 'sort_order', 'is_active', 'brand_id'];

    protected $casts = ['is_active' => 'boolean'];

    /** @return HasMany<Post, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function getLabelAttribute(): string
    {
        return $this->emoji ? $this->emoji.' '.$this->name : $this->name;
    }

    /**
     * @param  Builder<self>  $q
     * @return Builder<self>
     */
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }
}
