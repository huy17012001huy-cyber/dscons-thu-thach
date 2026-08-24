<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\HasBrand;

class DaKhongCucLog extends Model {
    use HasBrand;
    protected $table = 'da_khong_cuc_log';
    protected $fillable = ['user_id','delta','reason','awarded_by','brand_id'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function awardedBy(): BelongsTo { return $this->belongsTo(User::class, 'awarded_by'); }
}
