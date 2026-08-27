<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasBrand;

    protected $fillable = ['user_id', 'title', 'body', 'pillar', 'status', 'is_anonymous', 'is_paid', 'paid_aip_amount', 'brand_id'];

    protected $casts = ['is_anonymous' => 'boolean', 'is_paid' => 'boolean'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Answer, $this> */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function bestAnswer(): ?Answer
    {
        return $this->answers()->where('is_best', true)->first();
    }
}
