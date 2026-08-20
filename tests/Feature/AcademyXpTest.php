<?php

namespace Tests\Feature;

use App\Livewire\AcademyDetail;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use App\Models\XpTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class AcademyXpTest extends TestCase
{
    use RefreshDatabase;

    public function test_completing_the_last_lesson_awards_lesson_and_course_xp(): void
    {
        $user = User::factory()->create(['level' => 1, 'xp' => 0]);
        $course = Course::create([
            'title' => 'Khóa học kiểm thử',
            'description' => 'Khóa học dùng cho regression test.',
            'pillar' => 'delivery',
            'difficulty' => 'basic',
            'min_level' => 1,
            'xp_reward' => 500,
            'is_published' => true,
        ]);
        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Module 1',
            'order_index' => 0,
        ]);
        $lesson = Lesson::create([
            'module_id' => $module->id,
            'title' => 'Lesson 1',
            'xp_reward' => 25,
            'order_index' => 0,
            'is_locked_by_default' => false,
        ]);
        CourseEnrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => Carbon::now(),
        ]);

        Livewire::actingAs($user)
            ->test(AcademyDetail::class, ['id' => $course->id])
            ->call('completeLesson', $lesson->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $user->id,
            'type' => 'lesson_complete',
            'amount' => 10,
        ]);
        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $user->id,
            'type' => 'course_complete',
            'amount' => 50,
        ]);
        $this->assertSame(60, (int) $user->fresh()->xp);
        $this->assertSame(2, XpTransaction::where('user_id', $user->id)->count());
    }
}
