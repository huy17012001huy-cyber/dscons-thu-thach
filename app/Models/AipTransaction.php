<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AipTransaction extends Model {
    protected $fillable = ['user_id','amount','type','reason','reference_type','reference_id','expires_at'];
    protected $casts = ['expires_at'=>'datetime'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
