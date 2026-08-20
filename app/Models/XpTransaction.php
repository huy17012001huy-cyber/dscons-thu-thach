<?php
namespace App\Models;
use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class XpTransaction extends Model {
    use HasBrand;
    protected $fillable = ['user_id','amount','type','reference_type','reference_id','multiplier','description','brand_id'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function reference(): MorphTo { return $this->morphTo(); }
}
