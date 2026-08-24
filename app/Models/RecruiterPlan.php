<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\HasBrand;

class RecruiterPlan extends Model
{
    use HasBrand;
    protected $fillable = ['brand_id', 'name', 'description', 'contact_credits', 'duration_days', 'price', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function orders(): HasMany { return $this->hasMany(RecruiterOrder::class, 'plan_id'); }
}
