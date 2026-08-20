<?php
namespace App\Models;
use Database\Factories\MembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\HasBrand;

class Membership extends Model {
    /** @use HasFactory<MembershipFactory> */
    use HasFactory, HasBrand;
    protected $fillable = ['user_id','plan','tier','status','trial_ends_at','starts_at','expires_at','paid_amount','payment_ref','referred_by','brand_id'];
    protected $casts = ['trial_ends_at'=>'datetime','starts_at'=>'datetime','expires_at'=>'datetime'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function referrer(): BelongsTo { return $this->belongsTo(User::class, 'referred_by'); }
    public function isActive(): bool { return $this->status === 'active' && $this->expires_at?->isFuture(); }
    public function isTrial(): bool { return $this->status === 'trial' && $this->trial_ends_at?->isFuture(); }
    public function isPremium(): bool { return $this->tier === 'premium'; }
}
