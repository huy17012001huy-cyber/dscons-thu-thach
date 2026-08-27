<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;

class PillarStat extends Model
{
    use HasBrand;

    protected $fillable = ['pillar', 'post_count_7d', 'post_pct', 'is_burning', 'burning_started_at', 'last_calculated_at', 'brand_id'];

    protected $casts = ['is_burning' => 'boolean', 'burning_started_at' => 'datetime', 'last_calculated_at' => 'datetime'];

    public function getPillarLabelAttribute(): string
    {
        return app()->bound('brand') ? brand()->pillarLabel($this->pillar) : (string) $this->pillar;
    }
}
