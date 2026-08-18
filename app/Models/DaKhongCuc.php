<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DaKhongCuc extends Model {
    protected $table = 'da_khong_cuc';
    protected $fillable = ['user_id','total_count'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function logs(): HasMany { return $this->hasMany(DaKhongCucLog::class, 'user_id', 'user_id'); }
}
