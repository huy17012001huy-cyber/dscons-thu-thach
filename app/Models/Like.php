<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\HasBrand;

class Like extends Model {
    use SoftDeletes, HasBrand;
    protected $fillable = ['likeable_type','likeable_id','user_id','brand_id'];
    public function likeable(): MorphTo { return $this->morphTo(); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
