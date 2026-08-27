<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property int $total_count */
class DaKhongCuc extends Model
{
    use HasBrand;

    protected $table = 'da_khong_cuc';

    protected $fillable = ['user_id', 'total_count', 'brand_id'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<DaKhongCucLog, $this> */
    public function logs(): HasMany
    {
        return $this->hasMany(DaKhongCucLog::class, 'user_id', 'user_id');
    }
}
