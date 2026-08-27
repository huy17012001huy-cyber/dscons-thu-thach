<?php

declare(strict_types=1);

namespace Modules\Commerce\Application;

use App\Core\CommunityContext;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class CourseEnrollmentService
{
    public function __construct(private readonly CommunityContext $context) {}

    public function enroll(Course $course, User $user): CourseEnrollmentOutcome
    {
        return DB::transaction(function () use ($course, $user): CourseEnrollmentOutcome {
            $course = $this->lockedCourse($course);
            if (! $course->is_published) {
                return CourseEnrollmentOutcome::Unavailable;
            }
            if ($user->level < $course->min_level) {
                return CourseEnrollmentOutcome::LevelLocked;
            }

            $enrollment = $this->lockedEnrollment($course, $user);
            if ($enrollment?->status === 'active') {
                return CourseEnrollmentOutcome::AlreadyActive;
            }
            if ($enrollment) {
                return CourseEnrollmentOutcome::AlreadyPending;
            }

            $active = $user->hasPremiumMembership($course->brand_id) || $course->isFree();
            CourseEnrollment::create([
                'brand_id' => $course->brand_id,
                'user_id' => $user->id,
                'course_id' => $course->id,
                'status' => $active ? 'active' : 'pending_payment',
                'enrolled_at' => now(),
            ]);

            return $active ? CourseEnrollmentOutcome::Active : CourseEnrollmentOutcome::PendingPayment;
        });
    }

    public function ensurePremiumEnrollment(Course $course, User $user): CourseEnrollmentOutcome
    {
        return DB::transaction(function () use ($course, $user): CourseEnrollmentOutcome {
            $course = $this->lockedCourse($course);
            if (! $course->is_published || ! $user->hasPremiumMembership($course->brand_id)) {
                return CourseEnrollmentOutcome::Unavailable;
            }

            $enrollment = $this->lockedEnrollment($course, $user);
            if ($enrollment?->status === 'active') {
                return CourseEnrollmentOutcome::AlreadyActive;
            }
            if ($enrollment) {
                return CourseEnrollmentOutcome::AlreadyPending;
            }

            CourseEnrollment::create([
                'brand_id' => $course->brand_id,
                'user_id' => $user->id,
                'course_id' => $course->id,
                'status' => 'active',
                'enrolled_at' => now(),
            ]);

            return CourseEnrollmentOutcome::Active;
        });
    }

    private function lockedCourse(Course $course): Course
    {
        $brand = $this->context->require();
        if ($course->brand_id !== $brand->id) {
            throw new AuthorizationException('Course does not belong to the current community.');
        }

        return Course::query()
            ->where('brand_id', $brand->id)
            ->whereKey($course->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function lockedEnrollment(Course $course, User $user): ?CourseEnrollment
    {
        return CourseEnrollment::query()
            ->where('brand_id', $course->brand_id)
            ->where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->lockForUpdate()
            ->first();
    }
}
