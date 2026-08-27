<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaderboardSnapshot extends Model
{
    use HasBrand;

    public $timestamps = false;

    protected $fillable = ['user_id', 'period', 'period_key', 'xp_earned', 'rank', 'rank_change', 'snapshot_date', 'brand_id'];

    protected $casts = ['snapshot_date' => 'date'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
