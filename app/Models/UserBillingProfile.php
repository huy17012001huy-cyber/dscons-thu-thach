<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBillingProfile extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = [
        'user_id', 'type', 'full_name', 'company_name', 'invoice_email',
        'identity_number', 'tax_code', 'address', 'phone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
