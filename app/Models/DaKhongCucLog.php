<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DaKhongCucLog extends Model {
    protected $table = 'da_khong_cuc_log';
    protected $fillable = ['user_id','delta','reason','awarded_by'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function awardedBy(): BelongsTo { return $this->belongsTo(User::class, 'awarded_by'); }
}
