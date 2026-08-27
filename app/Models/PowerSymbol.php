<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PowerSymbol extends Model
{
    use HasBrand;

    protected $fillable = ['user_id', 'pillar', 'level', 'fragments', 'brand_id'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getEmoji(): string
    {
        $emojis = [
            'offer' => "\u{1F525}",
            'traffic' => "\u{2728}",
            'conversion' => "\u{1F3AF}",
            'delivery' => "\u{2699}\u{FE0F}",
            'continuity' => "\u{1F517}",
        ];

        return $emojis[(string) $this->pillar];
    }

    public function fragmentsForNextLevel(): int
    {
        $l = $this->level + 1;

        return $l * 10 + ($l - 1) * 15;
    }
}
