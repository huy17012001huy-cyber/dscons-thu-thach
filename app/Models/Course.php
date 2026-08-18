<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\HasBrand;

class Course extends Model
{
    use HasBrand;
    protected $fillable = [
        'title', 'description', 'pillar', 'difficulty', 'min_level',
        'xp_reward', 'aip_reward', 'price', 'thumbnail', 'is_published', 'brand_id',
    ];

    public function isFree(): bool
    {
        return $this->price <= 0;
    }

    protected $casts = ['is_published' => 'boolean'];

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('order_index');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }
}
