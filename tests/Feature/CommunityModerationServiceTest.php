<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\CommunityContext;
use App\Livewire\AdminCotReview;
use App\Livewire\AdminReports;
use App\Models\Brand;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Community\Application\CommunityModerationService;
use Tests\TestCase;

final class CommunityModerationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_moderator_can_review_and_remove_reported_content_in_its_community(): void
    {
        $moderator = $this->userWithRole('moderator');
        $post = $this->postFor(User::factory()->create());
        $report = Report::create([
            'brand_id' => brand()->id,
            'user_id' => User::factory()->create()->id,
            'reportable_type' => Post::class,
            'reportable_id' => $post->id,
            'reason' => 'Spam',
        ]);
        $service = app(CommunityModerationService::class);

        self::assertTrue($service->dismissReport($report->id, $moderator));
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'dismissed']);
        self::assertTrue($service->deleteReportedContent($report->id, $moderator));
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'reviewed']);
    }

    public function test_member_cannot_moderate_reports_or_approve_cot(): void
    {
        $member = $this->userWithRole('member');
        $post = $this->postFor(User::factory()->create(), ['cot_by' => User::factory()->create()->id]);
        $report = Report::create([
            'brand_id' => brand()->id,
            'user_id' => User::factory()->create()->id,
            'reportable_type' => Post::class,
            'reportable_id' => $post->id,
        ]);
        $service = app(CommunityModerationService::class);

        self::assertFalse($service->reviewReport($report->id, $member));
        self::assertFalse($service->approveCot($post->id, $member));
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'pending']);
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_cot' => false]);
    }

    public function test_community_admin_can_approve_a_pending_cot_once(): void
    {
        $admin = $this->userWithRole('admin');
        $owner = User::factory()->create();
        $post = $this->postFor($owner, ['cot_by' => User::factory()->create()->id]);
        $service = app(CommunityModerationService::class);

        self::assertTrue($service->approveCot($post->id, $admin));
        self::assertFalse($service->approveCot($post->id, $admin));
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_cot' => true]);
        $this->assertDatabaseHas('xp_transactions', ['user_id' => $owner->id, 'type' => 'cot']);
    }

    public function test_moderation_cannot_load_a_report_from_another_community(): void
    {
        $moderator = $this->userWithRole('moderator');
        $otherBrand = $this->otherBrand();
        $post = Post::withoutGlobalScopes()->create([
            'brand_id' => $otherBrand->id,
            'user_id' => User::factory()->create()->id,
            'content' => 'Other community post.',
            'pillar' => 'delivery',
        ]);
        $report = Report::withoutGlobalScopes()->create([
            'brand_id' => $otherBrand->id,
            'user_id' => User::factory()->create()->id,
            'reportable_type' => Post::class,
            'reportable_id' => $post->id,
        ]);

        $this->expectException(ModelNotFoundException::class);
        app(CommunityModerationService::class)->reviewReport($report->id, $moderator);
    }

    public function test_livewire_moderation_components_use_the_service(): void
    {
        $moderator = $this->userWithRole('moderator');
        $admin = $this->userWithRole('admin');
        $post = $this->postFor(User::factory()->create(), ['cot_by' => User::factory()->create()->id]);
        $report = Report::create([
            'brand_id' => brand()->id,
            'user_id' => User::factory()->create()->id,
            'reportable_type' => Post::class,
            'reportable_id' => $post->id,
        ]);

        Livewire::actingAs($moderator)->test(AdminReports::class)->call('reviewed', $report->id)->assertHasNoErrors();
        Livewire::actingAs($admin)->test(AdminCotReview::class)->call('approve', $post->id)->assertHasNoErrors();

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'reviewed']);
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_cot' => true]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->brandRoles()->attach(brand()->id, ['role' => $role]);

        return $user;
    }

    /** @param array<string, mixed> $attributes */
    private function postFor(User $owner, array $attributes = []): Post
    {
        return Post::factory()->create(array_merge([
            'brand_id' => app(CommunityContext::class)->require()->id,
            'user_id' => $owner->id,
        ], $attributes));
    }

    private function otherBrand(): Brand
    {
        return Brand::create([
            'name' => 'Other Moderation Community',
            'slug' => 'other-moderation-community',
            'domain' => 'other-moderation-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);
    }
}
