<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $event_id
 * @property int $user_id
 * @property string $status
 * @property CarbonInterface|null $registered_at
 * @property CarbonInterface|null $attended_at
 * @property-read User|null $user
 */
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

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_attended_by');
    }
}
