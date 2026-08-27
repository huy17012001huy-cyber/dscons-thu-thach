<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $digital_product_id
 * @property int $brand_id
 * @property string $status
 * @property DigitalProduct $product
 * @property User $user
 */
class ProductPurchase extends Model
{
    use HasBrand;

    protected $fillable = [
        'user_id', 'digital_product_id', 'status',
        'payment_ref', 'amount_paid', 'paid_at', 'brand_id',
    ];

    protected $casts = ['paid_at' => 'datetime'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<DigitalProduct, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(DigitalProduct::class, 'digital_product_id');
    }
}
