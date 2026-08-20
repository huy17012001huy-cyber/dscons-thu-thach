<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    use HasBrand;

    protected $fillable = [
        'brand_id', 'event_id', 'user_id', 'status', 'registered_at',
        'attended_at', 'marked_attended_by',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'attended_at' => 'datetime',
    ];

    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function markedBy(): BelongsTo { return $this->belongsTo(User::class, 'marked_attended_by'); }
}
