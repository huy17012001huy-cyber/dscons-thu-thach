<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Course;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Modules\Community\Application\EventManagementData;
use Modules\Community\Application\EventManagementService;
use Tests\TestCase;

final class EventManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_admin_can_create_publish_and_complete_an_event(): void
    {
        $admin = $this->admin();
        $course = $this->course();
        $service = app(EventManagementService::class);
        $event = $service->save(null, $admin, $this->data($course->id));

        self::assertSame(brand()->id, $event->brand_id);
        self::assertSame('draft', $event->status);
        self::assertTrue($service->publish($event->id, $admin));
        self::assertTrue($service->updateStatus($event->id, $admin, 'completed'));
        $this->assertDatabaseHas('events', ['id' => $event->id, 'status' => 'completed']);
    }

    public function test_event_service_rejects_resources_and_events_from_another_community(): void
    {
        $admin = $this->admin();
        $otherBrand = $this->otherBrand();
        $foreignCourse = Course::withoutGlobalScopes()->create([
            'brand_id' => $otherBrand->id,
            'title' => 'Foreign course',
            'pillar' => 'delivery',
            'difficulty' => 'basic',
            'is_published' => true,
        ]);
        $service = app(EventManagementService::class);

        $this->expectException(InvalidArgumentException::class);
        $service->save(null, $admin, $this->data($foreignCourse->id));
    }

    public function test_attendance_is_limited_to_an_event_registration_in_the_current_community(): void
    {
        $admin = $this->admin();
        $event = $this->event($this->course());
        $registration = EventRegistration::create([
            'brand_id' => brand()->id,
            'event_id' => $event->id,
            'user_id' => User::factory()->create()->id,
            'status' => 'registered',
            'registered_at' => now(),
        ]);

        self::assertTrue(app(EventManagementService::class)->toggleAttendance($registration->id, $admin));
        $this->assertDatabaseMissing('event_registrations', ['id' => $registration->id, 'attended_at' => null]);
        self::assertTrue(app(EventManagementService::class)->toggleAttendance($registration->id, $admin));
        $this->assertDatabaseHas('event_registrations', ['id' => $registration->id, 'attended_at' => null]);
    }

    public function test_event_management_cannot_load_an_event_from_another_community(): void
    {
        $admin = $this->admin();
        $otherBrand = $this->otherBrand();
        $event = Event::withoutGlobalScopes()->create([
            'brand_id' => $otherBrand->id,
            'course_id' => Course::withoutGlobalScopes()->create([
                'brand_id' => $otherBrand->id,
                'title' => 'Other course',
                'pillar' => 'delivery',
                'difficulty' => 'basic',
                'is_published' => true,
            ])->id,
            'created_by' => $admin->id,
            'title' => 'Other event',
            'event_type' => 'workshop',
            'format' => 'online',
            'starts_at' => now(),
            'ends_at' => now()->addHour(),
            'meeting_url' => 'https://meet.google.com/other',
            'status' => 'draft',
        ]);

        $this->expectException(ModelNotFoundException::class);
        app(EventManagementService::class)->publish($event->id, $admin);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);

        return $admin;
    }

    private function course(): Course
    {
        return Course::create([
            'brand_id' => brand()->id,
            'title' => 'Event management course',
            'pillar' => 'delivery',
            'difficulty' => 'basic',
            'is_published' => true,
        ]);
    }

    private function event(Course $course): Event
    {
        return Event::create([
            'brand_id' => brand()->id,
            'course_id' => $course->id,
            'created_by' => User::factory()->create()->id,
            'title' => 'Event management event',
            'event_type' => 'workshop',
            'format' => 'online',
            'starts_at' => now(),
            'ends_at' => now()->addHour(),
            'meeting_url' => 'https://meet.google.com/test',
            'status' => 'draft',
        ]);
    }

    private function data(int $courseId): EventManagementData
    {
        return new EventManagementData(
            title: 'Managed event',
            description: 'Managed event description',
            eventType: 'workshop',
            format: 'online',
            startsAt: Carbon::now()->addHour(),
            endsAt: Carbon::now()->addHours(2),
            status: 'draft',
            courseId: $courseId,
            meetingUrl: 'https://meet.google.com/test',
        );
    }

    private function otherBrand(): Brand
    {
        return Brand::create([
            'name' => 'Other Event Management Community',
            'slug' => 'other-event-management-community',
            'domain' => 'other-event-management-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);
    }
}
