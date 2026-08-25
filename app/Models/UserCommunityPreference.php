<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCommunityPreference extends \Illuminate\Database\Eloquent\Model
{
    use HasBrand;

    protected $fillable = ['user_id', 'brand_id', 'notifications_enabled'];

    protected $casts = ['notifications_enabled' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
