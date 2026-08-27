<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBillingProfile extends Model
{
    protected $fillable = [
        'user_id', 'type', 'full_name', 'company_name', 'invoice_email',
        'identity_number', 'tax_code', 'address', 'phone',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
