<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\HasBrand;

class Question extends Model {
    use HasBrand;
    protected $fillable = ['user_id','title','body','pillar','status','is_anonymous','is_paid','paid_aip_amount','brand_id'];
    protected $casts = ['is_anonymous'=>'boolean','is_paid'=>'boolean'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function answers(): HasMany { return $this->hasMany(Answer::class); }
    public function bestAnswer() { return $this->answers()->where('is_best', true)->first(); }
}
