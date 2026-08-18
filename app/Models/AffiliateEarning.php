<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\HasBrand;

class AffiliateEarning extends Model {
    use HasBrand;
    protected $fillable = ['referrer_id','referred_id','membership_id','amount','commission_rate','status','paid_at','brand_id'];
    protected $casts = ['paid_at'=>'datetime'];
    public function referrer(): BelongsTo { return $this->belongsTo(User::class, 'referrer_id'); }
    public function referred(): BelongsTo { return $this->belongsTo(User::class, 'referred_id'); }
    public function membership(): BelongsTo { return $this->belongsTo(Membership::class); }
}
