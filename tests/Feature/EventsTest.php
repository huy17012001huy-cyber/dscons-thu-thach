<?php

namespace Tests\Feature;

use App\Livewire\EventsPage;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EventsTest extends TestCase
{
    use RefreshDatabase;

    private function member(): User
    {
        $user = User::factory()->create(['class' => 'offer_architect']);
        Membership::factory()->active()->create(['user_id' => $user->id]);
        return $user;
    }

    private function course(): Course
    {
        return Course::create([
            'title' => 'Khóa học test sự kiện',
            'pillar' => 'delivery',
            'difficulty' => 'basic',
            'is_published' => true,
        ]);
    }

    private function eventFor(Course $course, string $status = 'published'): Event
    {
        return Event::create([
            'course_id' => $course->id,
            'created_by' => User::factory()->create()->id,
            'title' => 'Workshop test',
            'description' => 'Mô tả test',
            'event_type' => 'workshop',
            'format' => 'online',
            'starts_at' => now()->subMinutes(5),
            'ends_at' => now()->addHour(),
            'meeting_url' => 'https://meet.google.com/test',
            'status' => $status,
        ]);
    }

    public function test_guest_cannot_access_events(): void
    {
        $this->get('/su-kien')->assertRedirectToRoute('login');
    }

    public function test_draft_is_hidden_and_active_course_member_can_rsvp_and_join(): void
    {
        $user = $this->member();
        $course = $this->course();
        CourseEnrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        $draft = $this->eventFor($course, 'draft');
        $this->actingAs($user)->get('/su-kien')->assertDontSee($draft->title);

        $event = $this->eventFor($course);
        $component = Livewire::actingAs($user)->test(EventsPage::class);
        $component->assertDontSee($event->meeting_url);
        $component->call('registerEvent', $event->id);

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'registered',
        ]);
        $component->assertSee($event->meeting_url);
    }

    public function test_non_member_cannot_rsvp(): void
    {
        $user = $this->member();
        $event = $this->eventFor($this->course());

        Livewire::actingAs($user)->test(EventsPage::class)->call('registerEvent', $event->id);

        $this->assertDatabaseMissing('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);
    }
}
