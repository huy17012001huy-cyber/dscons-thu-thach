<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityUserStat extends Model
{
    use HasBrand;

    protected $fillable = [
        'brand_id', 'user_id', 'xp', 'level', 'aip', 'streak', 'last_active_at',
    ];

    protected $casts = [
        'xp' => 'integer',
        'level' => 'integer',
        'aip' => 'integer',
        'streak' => 'integer',
        'last_active_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
