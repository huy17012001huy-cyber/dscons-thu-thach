<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Commerce\Application\CourseEnrollmentOutcome;
use Modules\Commerce\Application\CourseEnrollmentService;
use Tests\TestCase;

final class CourseEnrollmentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrollment_creates_active_or_pending_state_once(): void
    {
        $user = User::factory()->create(['level' => 5]);
        $freeCourse = $this->course('Free course', 0);
        $paidCourse = $this->course('Paid course', 300000);
        $service = app(CourseEnrollmentService::class);

        self::assertSame(CourseEnrollmentOutcome::Active, $service->enroll($freeCourse, $user));
        self::assertSame(CourseEnrollmentOutcome::AlreadyActive, $service->enroll($freeCourse, $user));
        self::assertSame(CourseEnrollmentOutcome::PendingPayment, $service->enroll($paidCourse, $user));
        self::assertSame(CourseEnrollmentOutcome::AlreadyPending, $service->enroll($paidCourse, $user));

        $this->assertDatabaseHas('course_enrollments', [
            'brand_id' => brand()->id,
            'user_id' => $user->id,
            'course_id' => $freeCourse->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('course_enrollments', [
            'brand_id' => brand()->id,
            'user_id' => $user->id,
            'course_id' => $paidCourse->id,
            'status' => 'pending_payment',
        ]);
    }

    public function test_premium_membership_activates_a_paid_course_without_payment(): void
    {
        $user = User::factory()->create(['level' => 5]);
        Membership::create([
            'brand_id' => brand()->id,
            'user_id' => $user->id,
            'tier' => 'premium',
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
        ]);
        $course = $this->course('Premium course', 300000);

        self::assertSame(CourseEnrollmentOutcome::Active, app(CourseEnrollmentService::class)->enroll($course, $user));
        self::assertSame(CourseEnrollmentOutcome::AlreadyActive, app(CourseEnrollmentService::class)->ensurePremiumEnrollment($course, $user));
    }

    public function test_enrollment_respects_course_level_and_community_boundary(): void
    {
        $user = User::factory()->create(['level' => 1]);
        $lockedCourse = $this->course('Advanced course', 0, 2);

        self::assertSame(CourseEnrollmentOutcome::LevelLocked, app(CourseEnrollmentService::class)->enroll($lockedCourse, $user));
        $this->assertDatabaseCount('course_enrollments', 0);

        $otherCourse = Course::withoutGlobalScopes()->create([
            'brand_id' => $this->otherBrand()->id,
            'title' => 'Other community course',
            'pillar' => 'delivery',
            'difficulty' => 'basic',
            'min_level' => 1,
            'price' => 0,
            'is_published' => true,
        ]);

        $this->expectException(AuthorizationException::class);
        app(CourseEnrollmentService::class)->enroll($otherCourse, $user);
    }

    private function course(string $title, int $price, int $minLevel = 1): Course
    {
        return Course::create([
            'brand_id' => brand()->id,
            'title' => $title,
            'pillar' => 'delivery',
            'difficulty' => 'basic',
            'min_level' => $minLevel,
            'price' => $price,
            'is_published' => true,
        ]);
    }

    private function otherBrand(): Brand
    {
        return Brand::create([
            'name' => 'Other Course Community',
            'slug' => 'other-course-community',
            'domain' => 'other-course-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);
    }
}
