<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    // Bảng chỉ ghi log, không cần updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'brand_id', 'user_id', 'ip_address', 'user_agent',
        'via_admin', 'device_cookie_id', 'fingerprint_hash',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'via_admin'  => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
