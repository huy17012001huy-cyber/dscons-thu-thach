<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\HasBrand;

class CourseEnrollment extends Model
{
    use HasBrand;
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'course_id', 'status', 'payment_ref',
        'amount_paid', 'paid_at', 'enrolled_at', 'completed_at', 'brand_id',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
