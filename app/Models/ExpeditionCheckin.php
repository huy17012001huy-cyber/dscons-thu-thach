<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\HasBrand;

class ExpeditionCheckin extends Model {
    use HasBrand;
    protected $fillable = ['expedition_id','user_id','content','brand_id'];
    public function expedition(): BelongsTo { return $this->belongsTo(Expedition::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
