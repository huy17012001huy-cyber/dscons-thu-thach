<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\CommunityContext;
use App\Livewire\PostCard;
use App\Models\Brand;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Community\Application\PostInteractionService;
use Tests\TestCase;

final class PostInteractionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_like_toggle_preserves_the_first_reward_when_a_like_is_restored(): void
    {
        $owner = User::factory()->create();
        $actor = User::factory()->create();
        $post = $this->postFor($owner);
        $service = app(PostInteractionService::class);

        self::assertTrue($service->togglePostLike($post, $actor)->isActive);
        self::assertFalse($service->togglePostLike($post, $actor)->isActive);
        self::assertTrue($service->togglePostLike($post, $actor)->isActive);

        $this->assertDatabaseCount('likes', 1);
        $this->assertDatabaseHas('likes', [
            'brand_id' => brand()->id,
            'likeable_type' => Post::class,
            'likeable_id' => $post->id,
            'user_id' => $actor->id,
            'deleted_at' => null,
        ]);
        $this->assertDatabaseCount('xp_transactions', 1);
        $this->assertDatabaseHas('xp_transactions', ['user_id' => $owner->id, 'type' => 'post_liked']);
    }

    public function test_bookmarks_are_scoped_to_the_current_community(): void
    {
        $actor = User::factory()->create();
        $post = $this->postFor(User::factory()->create());

        self::assertTrue(app(PostInteractionService::class)->toggleBookmark($post, $actor)->isActive);
        self::assertFalse(app(PostInteractionService::class)->toggleBookmark($post, $actor)->isActive);

        $this->assertDatabaseMissing('bookmarks', ['user_id' => $actor->id, 'post_id' => $post->id]);
    }

    public function test_cross_community_posts_and_comments_cannot_be_interacted_with(): void
    {
        $owner = User::factory()->create();
        $actor = User::factory()->create();
        $otherBrand = Brand::create([
            'name' => 'Other Community',
            'slug' => 'other-community',
            'domain' => 'other-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);
        $post = Post::withoutGlobalScopes()->create([
            'brand_id' => $otherBrand->id,
            'user_id' => $owner->id,
            'content' => 'Other community post.',
            'pillar' => 'delivery',
        ]);
        $comment = Comment::withoutGlobalScopes()->create([
            'brand_id' => $otherBrand->id,
            'post_id' => $post->id,
            'user_id' => $owner->id,
            'content' => 'Other community comment.',
        ]);
        $service = app(PostInteractionService::class);

        try {
            $service->togglePostLike($post, $actor);
            self::fail('A post from another community must not be actionable.');
        } catch (AuthorizationException) {
        }

        $this->assertDatabaseMissing('likes', ['likeable_id' => $post->id]);
        $this->assertDatabaseMissing('likes', ['likeable_id' => $comment->id]);
    }

    public function test_comment_awards_rune_and_owner_exp_only_for_the_first_commenter_interaction(): void
    {
        $owner = User::factory()->create();
        $actor = User::factory()->create();
        $post = $this->postFor($owner, ['rune_active' => true, 'rune_expires_at' => now()->addHour()]);
        $service = app(PostInteractionService::class);

        $first = $service->addComment($post, $actor, 'First comment.');
        $second = $service->addComment($post, $actor, 'Second comment.');

        self::assertNotNull($first->comment);
        self::assertTrue($first->comment->is_rune_winner);
        self::assertNotNull($second->comment);
        self::assertFalse((bool) $second->comment->is_rune_winner);
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'rune_first_comment_user_id' => $actor->id]);
        $this->assertDatabaseCount('xp_transactions', 3);
        $this->assertDatabaseHas('xp_transactions', ['user_id' => $owner->id, 'type' => 'post_commented']);
    }

    public function test_post_card_uses_the_community_interaction_service(): void
    {
        $owner = User::factory()->create();
        $actor = User::factory()->create();
        $post = $this->postFor($owner);

        Livewire::actingAs($actor)
            ->test(PostCard::class, ['post' => $post])
            ->call('toggleLike')
            ->assertSet('isLiked', true)
            ->assertSet('likesCount', 1)
            ->call('toggleBookmark')
            ->assertSet('isBookmarked', true)
            ->set('newComment', 'Livewire comment')
            ->call('addComment')
            ->assertSet('newComment', '')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('likes', ['likeable_type' => Post::class, 'likeable_id' => $post->id, 'user_id' => $actor->id]);
        $this->assertDatabaseHas('bookmarks', ['post_id' => $post->id, 'user_id' => $actor->id]);
        $this->assertDatabaseHas('comments', ['post_id' => $post->id, 'user_id' => $actor->id, 'content' => 'Livewire comment']);
    }

    /** @param array<string, mixed> $attributes */
    private function postFor(User $owner, array $attributes = []): Post
    {
        return Post::factory()->create(array_merge([
            'brand_id' => app(CommunityContext::class)->require()->id,
            'user_id' => $owner->id,
        ], $attributes));
    }
}
