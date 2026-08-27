<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class CommerceWebhookEvent extends Model
{
    protected $fillable = [
        'provider',
        'external_event_id',
        'payload_hash',
        'payment_reference',
        'status',
        'processed_at',
    ];

    protected function casts(): array
    {
        return ['processed_at' => 'datetime'];
    }
}
