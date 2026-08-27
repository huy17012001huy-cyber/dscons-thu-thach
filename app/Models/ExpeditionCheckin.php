<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpeditionCheckin extends Model
{
    use HasBrand;

    protected $fillable = ['expedition_id', 'user_id', 'content', 'brand_id'];

    /** @return BelongsTo<Expedition, $this> */
    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
