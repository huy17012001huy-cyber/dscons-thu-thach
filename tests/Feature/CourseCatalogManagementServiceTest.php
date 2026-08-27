<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Learning\Application\CourseCatalogManagementService;
use Tests\TestCase;

final class CourseCatalogManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_admin_can_create_publish_and_delete_a_course(): void
    {
        $admin = $this->admin();
        $service = app(CourseCatalogManagementService::class);
        $course = $service->save(null, $admin, $this->attributes('Managed course'));

        self::assertNotNull($course);
        self::assertSame(brand()->id, $course->brand_id);
        self::assertFalse($course->is_published);
        self::assertTrue($service->togglePublished($course->id, $admin)->is_published);
        self::assertSame($course->id, $service->delete($course->id, $admin)->id);
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    public function test_course_management_cannot_target_another_community(): void
    {
        $admin = $this->admin();
        $other = Brand::create([
            'name' => 'Other course catalog community',
            'slug' => 'other-course-catalog-community',
            'domain' => 'other-course-catalog-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);
        $course = Course::withoutGlobalScopes()->create([
            ...$this->attributes('Other course'),
            'brand_id' => $other->id,
        ]);

        $this->expectException(ModelNotFoundException::class);
        app(CourseCatalogManagementService::class)->togglePublished($course->id, $admin);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);

        return $admin;
    }

    /** @return array<string, mixed> */
    private function attributes(string $title): array
    {
        return [
            'title' => $title,
            'description' => null,
            'pillar' => 'delivery',
            'difficulty' => 'basic',
            'min_level' => 1,
            'xp_reward' => 0,
            'aip_reward' => 0,
            'price' => 0,
            'is_published' => false,
            'is_featured' => false,
        ];
    }
}
