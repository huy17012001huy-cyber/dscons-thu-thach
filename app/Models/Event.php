<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasBrand;

    protected $fillable = [
        'brand_id', 'course_id', 'expedition_id', 'created_by', 'title', 'slug',
        'description', 'event_type', 'format', 'starts_at', 'ends_at',
        'meeting_url', 'location', 'capacity', 'status', 'access_tier',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'capacity' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $event): void {
            if (!$event->slug) {
                $event->slug = Str::slug($event->title) . '-' . Str::lower(Str::random(6));
            }
        });
    }

    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function expedition(): BelongsTo { return $this->belongsTo(Expedition::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function registrations(): HasMany { return $this->hasMany(EventRegistration::class); }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function isUpcoming(): bool { return $this->starts_at?->isFuture() ?? false; }
    public function isFinished(): bool { return $this->ends_at?->isPast() ?? false; }

    public function getDurationMinutesAttribute(): int
    {
        return max(0, (int) ($this->starts_at?->diffInMinutes($this->ends_at) ?? 0));
    }

    public function getRegisteredCountAttribute(): int
    {
        if (array_key_exists('registered_count', $this->attributes)) {
            return (int) $this->attributes['registered_count'];
        }

        return $this->registrations()->where('status', 'registered')->count();
    }

    public function isFull(): bool
    {
        return $this->capacity !== null && $this->registered_count >= $this->capacity;
    }

    public static function typeLabels(): array
    {
        return [
            'workshop' => 'Workshop',
            'live' => 'Livestream',
            'mentor' => 'Mentor meeting',
            'offline' => 'Offline',
            'other' => 'Sự kiện khác',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'draft' => 'Bản nháp',
            'published' => 'Đã công bố',
            'cancelled' => 'Đã hủy',
            'completed' => 'Đã hoàn thành',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return static::typeLabels()[$this->event_type] ?? 'Sự kiện';
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }
}
