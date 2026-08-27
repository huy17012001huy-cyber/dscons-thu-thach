<?php

namespace Tests\Feature;

use App\Livewire\CommunityManage;
use App\Livewire\AdminChallenges;
use App\Livewire\AdminCourses;
use App\Livewire\MembershipPricing;
use App\Models\Brand;
use App\Models\Course;
use App\Models\Expedition;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CommunityManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_owner_can_update_its_public_goal(): void
    {
        [$owner, $brand] = $this->ownerAndBrand();
        $this->actingAs($owner);
        app()->instance('brand', $brand);

        Livewire::test(CommunityManage::class)
            ->set('name', 'DSCons BIM Hub')
            ->set('tagline', 'Chuyển đổi năng lực kỹ sư MEP bằng BIM')
            ->set('description', 'Không gian học tập, thực hành và kết nối dành cho kỹ sư cơ điện.')
            ->call('save')
            ->assertHasNoErrors();

        $brand->refresh();
        $this->assertSame('DSCons BIM Hub', $brand->name);
        $this->assertSame('Chuyển đổi năng lực kỹ sư MEP bằng BIM', $brand->tagline);
        $this->assertSame('Không gian học tập, thực hành và kết nối dành cho kỹ sư cơ điện.', $brand->description);
    }

    public function test_owner_has_a_working_manage_route_and_preview_uses_the_saved_goal(): void
    {
        [$owner, $brand] = $this->ownerAndBrand();

        $this->actingAs($owner)
            ->get(route('community.manage', ['community' => $brand->slug]))
            ->assertOk()
            ->assertSee('Mục tiêu ngắn gọn');

        $this->get(route('community.preview', ['community' => $brand->slug]))
            ->assertOk()
            ->assertSee($brand->tagline)
            ->assertSee($brand->description);

        $this->get('/cong-dong/'.$brand->slug)
            ->assertRedirect('/c/'.$brand->slug);
    }

    public function test_member_cannot_open_community_manage_page(): void
    {
        [, $brand] = $this->ownerAndBrand();
        $member = User::factory()->create();
        $member->brandRoles()->attach($brand->id, ['role' => 'member']);
        Membership::withoutGlobalScopes()->create([
            'brand_id' => $brand->id,
            'user_id' => $member->id,
            'tier' => 'free',
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addYear(),
        ]);

        $this->actingAs($member)
            ->get(route('community.manage', ['community' => $brand->slug]))
            ->assertForbidden();
    }

    public function test_owner_can_create_a_challenge_with_a_cover_image(): void
    {
        [$owner, $brand] = $this->ownerAndBrand();
        Storage::fake('public');
        $this->actingAs($owner);
        app()->instance('brand', $brand);

        Livewire::test(AdminChallenges::class)
            ->set('expTitle', 'BIM Automation 21 ngày')
            ->set('expBossName', 'Hoàn thành một workflow BIM')
            ->set('expLeaderId', $owner->id)
            ->set('expCover', UploadedFile::fake()->createWithContent(
                'challenge-cover.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WlS/cAAAAAASUVORK5CYII=')
            ))
            ->call('saveExpedition')
            ->assertHasNoErrors();

        $challenge = Expedition::query()->where('title', 'BIM Automation 21 ngày')->firstOrFail();
        $this->assertSame($brand->id, $challenge->brand_id);
        $this->assertNotNull($challenge->cover_path);
        Storage::disk('public')->assertExists($challenge->cover_path);
    }

    public function test_owner_can_create_a_course_that_is_scoped_to_its_community(): void
    {
        [$owner, $brand] = $this->ownerAndBrand();
        Storage::fake('public');
        $this->actingAs($owner);
        app()->instance('brand', $brand);

        Livewire::test(AdminCourses::class)
            ->set('courseTitle', 'BIM Developer nền tảng')
            ->set('courseDescription', 'Xây nền tảng automation cho kỹ sư MEP.')
            ->set('coursePillar', 'delivery')
            ->set('courseDifficulty', 'basic')
            ->set('coursePublished', true)
            ->set('courseThumbnail', UploadedFile::fake()->createWithContent(
                'course-cover.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WlS/cAAAAAASUVORK5CYII=')
            ))
            ->call('saveCourse')
            ->assertHasNoErrors();

        $course = Course::query()->where('title', 'BIM Developer nền tảng')->firstOrFail();
        $this->assertSame($brand->id, $course->brand_id);
        $this->assertTrue($course->is_published);
        Storage::disk('public')->assertExists($course->thumbnail);
    }

    public function test_community_home_renders_published_courses_and_open_challenges(): void
    {
        [$owner, $brand] = $this->ownerAndBrand();
        app()->instance('brand', $brand);

        Course::create([
            'title' => 'Revit MEP thực chiến',
            'pillar' => 'delivery',
            'difficulty' => 'basic',
            'is_published' => true,
            'price' => 0,
        ]);
        Expedition::create([
            'title' => 'BIM Sprint 14 ngày',
            'slug' => 'bim-sprint-14-ngay',
            'boss_name' => 'Hoàn thành BIM Sprint',
            'leader_id' => $owner->id,
            'created_by' => $owner->id,
            'difficulty' => 'normal',
            'required_days' => 14,
            'max_members' => 50,
            'price' => 0,
            'status' => 'open',
        ]);

        $this->get(route('community.preview', ['community' => $brand->slug]))
            ->assertOk()
            ->assertSee('Revit MEP thực chiến')
            ->assertSee('BIM Sprint 14 ngày');
    }

    public function test_membership_pricing_explains_the_community_plan_and_only_sells_premium(): void
    {
        [$owner, $brand] = $this->ownerAndBrand();
        app()->instance('brand', $brand);
        $this->actingAs($owner);

        MembershipPlan::withoutGlobalScopes()->create([
            'brand_id' => $brand->id,
            'tier' => 'free',
            'name' => 'Free',
            'price' => 0,
            'status' => 'published',
        ]);
        $premium = MembershipPlan::withoutGlobalScopes()->create([
            'brand_id' => $brand->id,
            'tier' => 'premium',
            'name' => 'Premium 90 days',
            'duration_days' => 90,
            'price' => 990000,
            'status' => 'published',
        ]);

        Livewire::test(MembershipPricing::class)
            ->assertSee('Thành viên community')
            ->assertSee('Membership Premium')
            ->assertSee('Premium 90 days')
            ->assertDontSee('52 tuần')
            ->call('selectCommunityPlan', $premium->id)
            ->assertSee('Nội dung chuyển khoản')
            ->assertSee('MC'.$brand->id.'P'.$premium->id.'U'.$owner->id);
    }

    /** @return array{User, Brand} */
    private function ownerAndBrand(): array
    {
        $owner = User::factory()->create();
        $brand = Brand::create([
            'name' => 'Kỹ sư BIM',
            'slug' => 'ky-su-bim',
            'domain' => 'ky-su-bim.test',
            'owner_id' => $owner->id,
            'status' => 'active',
            'tagline' => 'Học BIM thực chiến',
            'description' => 'Một cộng đồng dành cho kỹ sư cơ điện.',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#E1F4F7',
            'theme_bg' => '#F7FAFC',
        ]);
        $owner->brandRoles()->attach($brand->id, ['role' => 'owner']);
        Membership::withoutGlobalScopes()->create([
            'brand_id' => $brand->id,
            'user_id' => $owner->id,
            'tier' => 'free',
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addYear(),
        ]);

        return [$owner, $brand];
    }
}
