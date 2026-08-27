<?php

namespace Tests\Feature;

use App\Core\Gamification\XpService;
use App\Models\Bookmark;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Membership;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a post
     */
    public function test_create_post(): void
    {
        $user = User::factory()->create(['class' => 'offer_architect', 'xp' => 0]);
        Membership::factory()->active()->create(['user_id' => $user->id]);

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Post',
            'content' => 'This is a test post',
            'pillar' => 'offer',
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'user_id' => $user->id,
            'title' => 'Test Post',
            'pillar' => 'offer',
        ]);
    }

    public function test_post_detail_has_a_unique_community_url_and_canonical_link(): void
    {
        $user = User::factory()->create();
        Membership::factory()->active()->create(['user_id' => $user->id]);
        $user->brandRoles()->attach(brand()->id, ['role' => 'member']);
        $post = Post::factory()->create([
            'brand_id' => brand()->id,
            'user_id' => $user->id,
            'title' => 'Bài viết có đường dẫn riêng',
            'slug' => 'bai-viet-co-duong-dan-rieng-123',
        ]);

        $this->actingAs($user)
            ->get('/c/dscons/bai-viet/'.$post->slug)
            ->assertOk()
            ->assertSee('Bài viết có đường dẫn riêng')
            ->assertSee('rel="canonical"', false)
            ->assertSee('/c/dscons/bai-viet/'.$post->slug, false);
    }

    /**
     * Test creating post awards XP
     */
    public function test_creating_post_awards_xp(): void
    {
        $user = User::factory()->create(['class' => 'traffic_mage', 'xp' => 0, 'streak' => 0]);
        Membership::factory()->active()->create(['user_id' => $user->id]);

        $xpService = app(XpService::class);
        $post = Post::factory()->create(['user_id' => $user->id]);

        $xp = $xpService->award($user, 'post', 1.0, 'Test post', $post);

        // post reward is 0 in XpService
        $this->assertEquals(0, $xp);
        $user->refresh();
        $this->assertEquals(0, $user->xp);
    }

    /**
     * Test post with COT status
     */
    public function test_post_with_cot_status(): void
    {
        $user = User::factory()->create();
        $curator = User::factory()->create();

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'is_cot' => true,
            'cot_by' => $curator->id,
            'cot_at' => now(),
        ]);

        $this->assertTrue($post->is_cot);
        $this->assertEquals($curator->id, $post->cot_by);
    }

    /**
     * Test post with signal status
     */
    public function test_post_with_signal_status(): void
    {
        $user = User::factory()->create();

        $post = Post::factory()
            ->signal()
            ->create(['user_id' => $user->id]);

        $this->assertTrue($post->is_signal);
    }

    /**
     * Test post with rune
     */
    public function test_post_with_rune(): void
    {
        $user = User::factory()->create();

        $post = Post::factory()
            ->withRune()
            ->create(['user_id' => $user->id]);

        $this->assertTrue($post->rune_active);
        $this->assertTrue($post->isRuneActive());
    }

    /**
     * Test rune expires after time window
     */
    public function test_rune_expires(): void
    {
        $user = User::factory()->create();

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'rune_active' => true,
            'rune_expires_at' => now()->subHour(),
        ]);

        $this->assertFalse($post->isRuneActive());
    }

    /**
     * Test like post
     */
    public function test_like_post(): void
    {
        $liker = User::factory()->create();
        $author = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id]);

        Like::create([
            'user_id' => $liker->id,
            'likeable_type' => Post::class,
            'likeable_id' => $post->id,
        ]);

        $this->assertDatabaseHas('likes', [
            'user_id' => $liker->id,
            'likeable_type' => Post::class,
            'likeable_id' => $post->id,
        ]);

        $this->assertTrue($post->isLikedBy($liker));
    }

    /**
     * Test unlike post
     */
    public function test_unlike_post(): void
    {
        $liker = User::factory()->create();
        $author = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id]);

        $like = Like::create([
            'user_id' => $liker->id,
            'likeable_type' => Post::class,
            'likeable_id' => $post->id,
        ]);

        $like->delete();

        $this->assertSoftDeleted('likes', [
            'id' => $like->id,
        ]);

        $this->assertFalse($post->fresh()->isLikedBy($liker));
    }

    /**
     * Test post pillar validation
     */
    public function test_post_pillar_labels(): void
    {
        $user = User::factory()->create();

        $pillars = brand()->pillarProfiles();

        foreach ($pillars as $pillar => $profile) {
            $post = Post::factory()->create([
                'user_id' => $user->id,
                'pillar' => $pillar,
            ]);

            $this->assertEquals($profile['name'], $post->pillar_label);
        }
    }

    /**
     * Test post pillar colors
     */
    public function test_post_pillar_colors(): void
    {
        $user = User::factory()->create();

        $colors = [
            'offer' => 'amber',
            'traffic' => 'purple',
            'conversion' => 'emerald',
            'delivery' => 'blue',
            'continuity' => 'red',
        ];

        foreach ($colors as $pillar => $color) {
            $post = Post::factory()->create([
                'user_id' => $user->id,
                'pillar' => $pillar,
            ]);

            $this->assertEquals($color, $post->pillar_color);
        }
    }

    /**
     * Test post user relationship
     */
    public function test_post_user_relationship(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $post->user->id);
    }

    /**
     * Test post comments relationship
     */
    public function test_post_comments_relationship(): void
    {
        $post = Post::factory()->create();

        $comment1 = Comment::factory()->create(['post_id' => $post->id, 'parent_id' => null]);
        $comment2 = Comment::factory()->create(['post_id' => $post->id, 'parent_id' => null]);
        $reply = Comment::factory()->create(['post_id' => $post->id, 'parent_id' => $comment1->id]);

        $this->assertEquals(2, $post->comments()->count());
        $this->assertEquals(3, $post->allComments()->count());
    }

    /**
     * Test post likes relationship
     */
    public function test_post_likes_relationship(): void
    {
        $post = Post::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Like::create(['user_id' => $user1->id, 'likeable_type' => Post::class, 'likeable_id' => $post->id]);
        Like::create(['user_id' => $user2->id, 'likeable_type' => Post::class, 'likeable_id' => $post->id]);

        $this->assertEquals(2, $post->likes()->count());
    }

    /**
     * Test post scopes
     */
    public function test_post_scopes(): void
    {
        $user = User::factory()->create();

        $normalPost = Post::factory()->create(['user_id' => $user->id, 'is_cot' => false, 'is_signal' => false, 'pillar' => 'offer']);
        $cotPost = Post::factory()->cot()->create(['user_id' => $user->id, 'pillar' => 'traffic']);
        $signalPost = Post::factory()->signal()->create(['user_id' => $user->id, 'pillar' => 'conversion']);

        $this->assertEquals(1, Post::cot()->count());
        $this->assertEquals(1, Post::signal()->count());
        $this->assertEquals(1, Post::byPillar('offer')->count());
    }

    /**
     * Test bookmark post
     */
    public function test_bookmark_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Bookmark::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        $this->assertTrue($post->isBookmarkedBy($user));
    }

    /**
     * Test post view count
     */
    public function test_post_view_count(): void
    {
        $post = Post::factory()->create(['view_count' => 0]);

        $post->increment('view_count');
        $post->increment('view_count');
        $post->refresh();

        $this->assertEquals(2, $post->view_count);
    }

    /**
     * Test multiple pillars have different posts
     */
    public function test_multiple_posts_different_pillars(): void
    {
        $user = User::factory()->create();

        $offerPost = Post::factory()->create(['user_id' => $user->id, 'pillar' => 'offer']);
        $trafficPost = Post::factory()->create(['user_id' => $user->id, 'pillar' => 'traffic']);
        $conversionPost = Post::factory()->create(['user_id' => $user->id, 'pillar' => 'conversion']);

        $this->assertEquals(1, Post::byPillar('offer')->count());
        $this->assertEquals(1, Post::byPillar('traffic')->count());
        $this->assertEquals(1, Post::byPillar('conversion')->count());
    }

    /**
     * Test post with topic
     */
    public function test_post_with_topic(): void
    {
        $user = User::factory()->create();
        $topic = Topic::factory()->create();

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'topic_id' => $topic->id,
        ]);

        $this->assertEquals($topic->id, $post->topic_id);
        $this->assertEquals($topic->id, $post->topic->id);
    }

    /**
     * Test post soft delete
     */
    public function test_post_soft_delete(): void
    {
        $post = Post::factory()->create();

        $post->delete();

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    /**
     * Test COT post awards more XP
     */
    public function test_cot_post_awards_more_xp(): void
    {
        $user = User::factory()->create(['xp' => 0, 'streak' => 0]);

        $xpService = app(XpService::class);

        // Regular post: 0 XP
        $post = Post::factory()->create(['user_id' => $user->id]);
        $xpService->award($user, 'post', 1.0, 'Post', $post);
        $user->refresh();
        $this->assertEquals(0, $user->xp);

        // COT post: 50 XP
        $cotPost = Post::factory()->cot()->create(['user_id' => $user->id]);
        $xpService->award($user, 'cot', 1.0, 'COT post', $cotPost);
        $user->refresh();
        $this->assertEquals(50, $user->xp);
    }
}
