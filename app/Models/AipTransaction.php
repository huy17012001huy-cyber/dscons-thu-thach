<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AipTransaction extends Model
{
    use HasBrand;

    protected $fillable = ['user_id', 'amount', 'type', 'reason', 'reference_type', 'reference_id', 'expires_at', 'brand_id'];

    protected $casts = ['expires_at' => 'datetime'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
