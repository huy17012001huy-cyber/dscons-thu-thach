<?php

namespace App\Models;

use Database\Factories\TopicFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\HasBrand;

class Topic extends Model
{
    /** @use HasFactory<TopicFactory> */
    use HasFactory, HasBrand;

    protected $fillable = ['name', 'emoji', 'slug', 'sort_order', 'is_active', 'brand_id'];

    protected $casts = ['is_active' => 'boolean'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function getLabelAttribute(): string
    {
        return $this->emoji ? $this->emoji . ' ' . $this->name : $this->name;
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }
}
