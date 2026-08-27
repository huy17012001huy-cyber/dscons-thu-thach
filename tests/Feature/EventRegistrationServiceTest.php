<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Event;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Community\Application\EventRegistrationOutcome;
use Modules\Community\Application\EventRegistrationService;
use Tests\TestCase;

final class EventRegistrationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_course_member_can_register_once_and_cancel(): void
    {
        $user = $this->member();
        $course = $this->course();
        CourseEnrollment::create([
            'brand_id' => brand()->id,
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);
        $event = $this->event($course);
        $service = app(EventRegistrationService::class);

        self::assertSame(EventRegistrationOutcome::Registered, $service->register($event->id, $user));
        self::assertSame(EventRegistrationOutcome::AlreadyRegistered, $service->register($event->id, $user));
        self::assertTrue($service->cancel($event->id, $user));
        $this->assertDatabaseHas('event_registrations', [
            'brand_id' => brand()->id,
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_registration_respects_capacity_and_eligibility(): void
    {
        $course = $this->course();
        $first = $this->enrolledMember($course);
        $second = $this->enrolledMember($course);
        $unrelated = $this->member();
        $event = $this->event($course, 1);
        $service = app(EventRegistrationService::class);

        self::assertSame(EventRegistrationOutcome::NotEligible, $service->register($event->id, $unrelated));
        self::assertSame(EventRegistrationOutcome::Registered, $service->register($event->id, $first));
        self::assertSame(EventRegistrationOutcome::Full, $service->register($event->id, $second));
    }

    public function test_registration_cannot_target_an_event_from_another_community(): void
    {
        $user = $this->member();
        $otherBrand = $this->otherBrand();
        $course = Course::withoutGlobalScopes()->create([
            'brand_id' => $otherBrand->id,
            'title' => 'Other course',
            'pillar' => 'delivery',
            'difficulty' => 'basic',
            'is_published' => true,
        ]);
        $event = Event::withoutGlobalScopes()->create([
            'brand_id' => $otherBrand->id,
            'course_id' => $course->id,
            'created_by' => User::factory()->create()->id,
            'title' => 'Other event',
            'event_type' => 'workshop',
            'format' => 'online',
            'starts_at' => now(),
            'ends_at' => now()->addHour(),
            'meeting_url' => 'https://meet.google.com/other',
            'status' => 'published',
        ]);

        $this->expectException(ModelNotFoundException::class);
        app(EventRegistrationService::class)->register($event->id, $user);
    }

    private function member(): User
    {
        $user = User::factory()->create();
        Membership::factory()->active()->create(['brand_id' => brand()->id, 'user_id' => $user->id]);
        $user->brandRoles()->attach(brand()->id, ['role' => 'member']);

        return $user;
    }

    private function enrolledMember(Course $course): User
    {
        $user = $this->member();
        CourseEnrollment::create([
            'brand_id' => brand()->id,
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        return $user;
    }

    private function course(): Course
    {
        return Course::create([
            'brand_id' => brand()->id,
            'title' => 'Event course',
            'pillar' => 'delivery',
            'difficulty' => 'basic',
            'is_published' => true,
        ]);
    }

    private function event(Course $course, ?int $capacity = null): Event
    {
        return Event::create([
            'brand_id' => brand()->id,
            'course_id' => $course->id,
            'created_by' => User::factory()->create()->id,
            'title' => 'Event registration test',
            'event_type' => 'workshop',
            'format' => 'online',
            'starts_at' => now(),
            'ends_at' => now()->addHour(),
            'meeting_url' => 'https://meet.google.com/test',
            'capacity' => $capacity,
            'status' => 'published',
        ]);
    }

    private function otherBrand(): Brand
    {
        return Brand::create([
            'name' => 'Other Event Community',
            'slug' => 'other-event-community',
            'domain' => 'other-event-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);
    }
}
