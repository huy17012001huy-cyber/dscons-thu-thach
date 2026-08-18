<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasBrand;

class CommunityChallenge extends Model {
    use HasBrand;
    protected $fillable = ['title','description','target_type','target_value','current_value','reward_xp','reward_aip','week_start','week_end','completed_at','brand_id'];
    protected $casts = ['week_start'=>'date','week_end'=>'date','completed_at'=>'datetime'];
    public function getProgressPctAttribute(): float {
        if ($this->target_value === 0) return 0;
        return min(100, round($this->current_value / $this->target_value * 100, 1));
    }
    public function isCompleted(): bool { return $this->completed_at !== null; }
}
