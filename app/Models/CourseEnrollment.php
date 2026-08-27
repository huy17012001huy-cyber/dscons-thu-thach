<?php

namespace App\Models;

use App\Models\Concerns\HasBrand;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property User $user
 * @property Course|null $course
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $enrolled_at
 */
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

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
