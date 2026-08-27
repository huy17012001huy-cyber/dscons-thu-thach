<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Learning\Application\CourseCurriculumManagementService;
use Tests\TestCase;

final class CourseCurriculumManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_admin_can_manage_only_the_current_course_curriculum(): void
    {
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);
        $course = $this->course();
        $service = app(CourseCurriculumManagementService::class);

        $module = $service->addModule($course, $admin, 'Module one');
        $lesson = $service->addLesson($course, $admin, $module->id, [
            'title' => 'Lesson one',
            'lesson_type' => 'lecture',
            'xp_reward' => 25,
            'is_locked_by_default' => true,
        ]);
        $task = $service->addTask($course, $admin, $lesson->id, [
            'title' => 'Task one',
            'description' => null,
            'type' => 'text',
            'is_required' => true,
        ]);

        self::assertSame(0, $module->order_index);
        self::assertSame(0, $lesson->order_index);
        self::assertSame(0, $task->order_index);
        self::assertTrue($service->deleteTask($course, $admin, $task->id));
        self::assertTrue($service->deleteLesson($course, $admin, $lesson->id));
        self::assertTrue($service->deleteModule($course, $admin, $module->id));
    }

    public function test_curriculum_cannot_be_managed_across_communities(): void
    {
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);
        $other = Brand::create([
            'name' => 'Other curriculum community',
            'slug' => 'other-curriculum-community',
            'domain' => 'other-curriculum-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);
        $course = Course::withoutGlobalScopes()->create([
            'brand_id' => $other->id,
            'title' => 'Other course',
            'pillar' => 'delivery',
            'difficulty' => 'basic',
            'is_published' => true,
        ]);

        $this->expectException(AuthorizationException::class);
        app(CourseCurriculumManagementService::class)->addModule($course, $admin, 'Blocked module');
    }

    private function course(): Course
    {
        return Course::create([
            'brand_id' => brand()->id,
            'title' => 'Curriculum course',
            'pillar' => 'delivery',
            'difficulty' => 'basic',
            'is_published' => true,
        ]);
    }
}
