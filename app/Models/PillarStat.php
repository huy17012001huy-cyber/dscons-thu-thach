<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasBrand;

class PillarStat extends Model {
    use HasBrand;
    protected $fillable = ['pillar','post_count_7d','post_pct','is_burning','burning_started_at','last_calculated_at','brand_id'];
    protected $casts = ['is_burning'=>'boolean','burning_started_at'=>'datetime','last_calculated_at'=>'datetime'];
    public function getPillarLabelAttribute(): string {
        return match($this->pillar) {
            'offer'=>'Offer','traffic'=>'Thu hút','conversion'=>'Chuyển đổi',
            'delivery'=>'Cung ứng','continuity'=>'Continuity',default=>$this->pillar,
        };
    }
}
