<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** @property int $id */
class Like extends Model
{
    use HasBrand, SoftDeletes;

    protected $fillable = ['likeable_type', 'likeable_id', 'user_id', 'brand_id'];

    /** @return MorphTo<Model, $this> */
    public function likeable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
