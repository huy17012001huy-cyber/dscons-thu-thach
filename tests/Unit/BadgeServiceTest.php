<?php

namespace Tests\Unit;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use App\Services\BadgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BadgeServiceTest extends TestCase
{
    use RefreshDatabase;

    private BadgeService $badgeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->badgeService = app(BadgeService::class);
    }

    /**
     * Test check awards badge when level condition met
     */
    public function test_check_awards_badge_on_level(): void
    {
        $badge = Badge::create([
            'name' => 'Level 5 Master',
            'slug' => 'level-5-master',
            'description' => 'Reach level 5',
            'condition_type' => 'level_gte',
            'condition_value' => 5,
        ]);

        $user = User::factory()->create(['level' => 5]);

        $this->badgeService->check($user);

        $this->assertDatabaseHas('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge->id,
        ]);
    }

    /**
     * Test check doesn't award badge when level condition not met
     */
    public function test_check_does_not_award_when_level_too_low(): void
    {
        $badge = Badge::create([
            'name' => 'Level 10 Master',
            'slug' => 'level-10-master',
            'description' => 'Reach level 10',
            'condition_type' => 'level_gte',
            'condition_value' => 10,
        ]);

        $user = User::factory()->create(['level' => 5]);

        $this->badgeService->check($user);

        $this->assertDatabaseMissing('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge->id,
        ]);
    }

    /**
     * Test check doesn't award duplicate badges
     */
    public function test_check_does_not_award_duplicate_badges(): void
    {
        $badge = Badge::create([
            'name' => 'Level 5 Master',
            'slug' => 'level-5-master',
            'description' => 'Reach level 5',
            'condition_type' => 'level_gte',
            'condition_value' => 5,
        ]);

        $user = User::factory()->create(['level' => 5]);

        // First check
        $this->badgeService->check($user);
        $count = UserBadge::where('user_id', $user->id)->where('badge_id', $badge->id)->count();
        $this->assertEquals(1, $count);

        // Second check
        $this->badgeService->check($user);
        $count = UserBadge::where('user_id', $user->id)->where('badge_id', $badge->id)->count();
        $this->assertEquals(1, $count); // Still 1, no duplicate
    }

    /**
     * Test check awards badge for post count
     */
    public function test_check_awards_badge_on_post_count(): void
    {
        $badge = Badge::create([
            'name' => '10 Posts',
            'slug' => '10-posts',
            'description' => 'Post 10 times',
            'condition_type' => 'post_count_gte',
            'condition_value' => 10,
        ]);

        $user = User::factory()->create();
        \App\Models\Post::factory()->count(10)->create(['user_id' => $user->id]);

        $this->badgeService->check($user);

        $this->assertDatabaseHas('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge->id,
        ]);
    }

    /**
     * Test check awards badge for comment count
     */
    public function test_check_awards_badge_on_comment_count(): void
    {
        $badge = Badge::create([
            'name' => '20 Comments',
            'slug' => '20-comments',
            'description' => 'Comment 20 times',
            'condition_type' => 'comment_count_gte',
            'condition_value' => 20,
        ]);

        $user = User::factory()->create();
        $post = \App\Models\Post::factory()->create();
        \App\Models\Comment::factory()->count(20)->create(['user_id' => $user->id, 'post_id' => $post->id]);

        $this->badgeService->check($user);

        $this->assertDatabaseHas('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge->id,
        ]);
    }

    /**
     * Test check awards badge for streak
     */
    public function test_check_awards_badge_on_streak(): void
    {
        $badge = Badge::create([
            'name' => '7 Day Streak',
            'slug' => '7-day-streak',
            'description' => 'Maintain 7 day streak',
            'condition_type' => 'streak_gte',
            'condition_value' => 7,
        ]);

        $user = User::factory()->create(['streak' => 7]);

        $this->badgeService->check($user);

        $this->assertDatabaseHas('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge->id,
        ]);
    }

    /**
     * Test check awards badge for bookmark count
     */
    public function test_check_awards_badge_on_bookmark_count(): void
    {
        $badge = Badge::create([
            'name' => '5 Bookmarks',
            'slug' => '5-bookmarks',
            'description' => 'Bookmark 5 posts',
            'condition_type' => 'bookmark_count_gte',
            'condition_value' => 5,
        ]);

        $user = User::factory()->create();
        $posts = \App\Models\Post::factory()->count(5)->create();
        foreach ($posts as $post) {
            \App\Models\Bookmark::create(['user_id' => $user->id, 'post_id' => $post->id]);
        }

        $this->badgeService->check($user);

        $this->assertDatabaseHas('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge->id,
        ]);
    }

    /**
     * Test check skips badge with unknown condition type
     */
    public function test_check_skips_unknown_condition_type(): void
    {
        $badge = Badge::create([
            'name' => 'Unknown Badge',
            'slug' => 'unknown-badge',
            'description' => 'Unknown',
            'condition_type' => 'unknown_condition',
            'condition_value' => 5,
        ]);

        $user = User::factory()->create(['level' => 100]);

        $this->badgeService->check($user);

        $this->assertDatabaseMissing('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge->id,
        ]);
    }

    /**
     * Test check skips badges with missing conditions
     */
    public function test_check_skips_badge_with_no_condition(): void
    {
        $badge = Badge::create([
            'name' => 'Invalid Badge',
            'slug' => 'invalid-badge',
            'description' => 'No condition',
            'condition_type' => null,
            'condition_value' => null,
        ]);

        $user = User::factory()->create(['level' => 100]);

        $this->badgeService->check($user);

        $this->assertDatabaseMissing('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge->id,
        ]);
    }

    /**
     * Test award method directly
     */
    public function test_award_creates_user_badge(): void
    {
        $badge = Badge::create([
            'name' => 'Test Badge',
            'slug' => 'test-badge',
            'description' => 'Test',
            'condition_type' => 'level_gte',
            'condition_value' => 5,
        ]);

        $user = User::factory()->create();

        $this->badgeService->award($user, $badge);

        $this->assertDatabaseHas('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge->id,
        ]);
    }

    /**
     * Test award doesn't create duplicate via public method
     */
    public function test_award_public_prevents_duplicates(): void
    {
        $badge = Badge::create([
            'name' => 'Test Badge',
            'slug' => 'test-badge',
            'description' => 'Test',
            'condition_type' => 'level_gte',
            'condition_value' => 5,
        ]);

        $user = User::factory()->create();

        // Award twice
        $this->badgeService->award($user, $badge);
        $this->badgeService->award($user, $badge);

        $count = UserBadge::where('user_id', $user->id)->where('badge_id', $badge->id)->count();
        $this->assertEquals(1, $count);
    }

    /**
     * Test check with multiple badge types
     */
    public function test_check_with_multiple_conditions(): void
    {
        $badge1 = Badge::create([
            'name' => 'Level 5',
            'slug' => 'level-5',
            'description' => 'Level 5',
            'condition_type' => 'level_gte',
            'condition_value' => 5,
        ]);

        $badge2 = Badge::create([
            'name' => '5 Posts',
            'slug' => '5-posts',
            'description' => '5 Posts',
            'condition_type' => 'post_count_gte',
            'condition_value' => 5,
        ]);

        $user = User::factory()->create(['level' => 5]);
        \App\Models\Post::factory()->count(5)->create(['user_id' => $user->id]);

        $this->badgeService->check($user);

        $this->assertDatabaseHas('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge1->id,
        ]);

        $this->assertDatabaseHas('user_badges', [
            'user_id' => $user->id,
            'badge_id' => $badge2->id,
        ]);
    }
}
