<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $brand_id
 * @property string $title
 * @property float|int $price
 * @property bool $is_featured
 * @property-read Brand|null $brand
 * @property-read int $enrollments_count
 * @property-read int $modules_count
 */
class Course extends Model
{
    use HasBrand;

    protected $fillable = [
        'title', 'description', 'pillar', 'difficulty', 'min_level',
        'xp_reward', 'aip_reward', 'price', 'thumbnail', 'is_published', 'access_tier', 'brand_id', 'is_featured',
    ];

    public function isFree(): bool
    {
        return $this->price <= 0;
    }

    protected $casts = ['is_published' => 'boolean', 'is_featured' => 'boolean'];

    /** @return HasMany<Module, $this> */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('order_index');
    }

    /** @return HasMany<CourseEnrollment, $this> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /** @return HasMany<Event, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
