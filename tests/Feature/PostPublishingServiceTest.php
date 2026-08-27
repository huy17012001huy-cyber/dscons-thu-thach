<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\CommunitySubject;
use App\Models\Membership;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Community\Application\PostPublishData;
use Modules\Community\Application\PostPublishingService;
use Tests\TestCase;

final class PostPublishingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_publishes_a_post_and_its_images_for_a_community_member(): void
    {
        $user = $this->member();
        $topic = Topic::factory()->create(['brand_id' => brand()->id]);
        $subject = CommunitySubject::create(['brand_id' => brand()->id, 'name' => 'Thiết kế', 'slug' => 'thiet-ke']);
        $outcome = app(PostPublishingService::class)->publish($user, new PostPublishData(
            title: 'BIM coordination',
            content: 'A useful post for the DSCons community.',
            pillar: 'offer',
            topicId: $topic->id,
            subjectId: $subject->id,
            imagePaths: ['post-images/one.png', 'post-images/two.png'],
        ), 5);

        self::assertNull($outcome->error);
        self::assertNotNull($outcome->post);
        $this->assertDatabaseHas('posts', ['id' => $outcome->post->id, 'brand_id' => brand()->id, 'user_id' => $user->id]);
        $this->assertDatabaseCount('post_images', 2);
    }

    public function test_service_rejects_users_who_do_not_belong_to_the_community(): void
    {
        $outcome = app(PostPublishingService::class)->publish(User::factory()->create(), $this->draft(), 5);

        self::assertNotNull($outcome->error);
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_service_rejects_taxonomy_from_another_community(): void
    {
        $user = $this->member();
        $otherBrand = $this->otherBrand();
        $foreignTopic = Topic::withoutGlobalScopes()->create([
            'brand_id' => $otherBrand->id,
            'name' => 'Foreign topic',
            'slug' => 'foreign-topic',
            'is_active' => true,
        ]);
        $outcome = app(PostPublishingService::class)->publish($user, new PostPublishData(
            content: 'A useful post for the DSCons community.',
            pillar: 'offer',
            topicId: $foreignTopic->id,
        ), 5);

        self::assertNotNull($outcome->error);
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_daily_limit_is_scoped_to_the_current_community(): void
    {
        $user = $this->member();
        Post::factory()->count(5)->create(['brand_id' => brand()->id, 'user_id' => $user->id]);
        $outcome = app(PostPublishingService::class)->publish($user, $this->draft(), 5);

        self::assertNotNull($outcome->error);
        $this->assertDatabaseCount('posts', 5);
    }

    private function member(): User
    {
        $user = User::factory()->create();
        Membership::factory()->active()->create(['brand_id' => brand()->id, 'user_id' => $user->id]);
        $user->brandRoles()->attach(brand()->id, ['role' => 'member']);

        return $user;
    }

    private function draft(): PostPublishData
    {
        return new PostPublishData(content: 'A useful post for the DSCons community.', pillar: 'offer');
    }

    private function otherBrand(): Brand
    {
        return Brand::create([
            'name' => 'Other Publishing Community',
            'slug' => 'other-publishing-community',
            'domain' => 'other-publishing-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);
    }
}
