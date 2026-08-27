<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Modules\Community\Application\CommunityTopicData;
use Modules\Community\Application\CommunityTopicService;
use Tests\TestCase;

final class CommunityTopicServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_admin_can_manage_a_topic_in_its_community(): void
    {
        $admin = $this->admin();
        $service = app(CommunityTopicService::class);
        $topic = $service->save(null, $admin, $this->data('BIM Coordination', 'bim-coordination'));

        self::assertSame(brand()->id, $topic->brand_id);
        self::assertTrue($service->toggleActive($topic->id, $admin));
        self::assertTrue($service->delete($topic->id, $admin));
        $this->assertDatabaseMissing('topics', ['id' => $topic->id]);
    }

    public function test_topic_slug_is_unique_per_community_not_globally(): void
    {
        $admin = $this->admin();
        $service = app(CommunityTopicService::class);
        $service->save(null, $admin, $this->data('BIM Coordination', 'bim-coordination'));
        $otherBrand = $this->otherBrand();
        Topic::withoutGlobalScopes()->create([
            'brand_id' => $otherBrand->id,
            'name' => 'Other BIM Coordination',
            'slug' => 'bim-coordination',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $service->save(null, $admin, $this->data('Duplicate BIM', 'bim-coordination'));
    }

    public function test_topic_service_cannot_edit_or_delete_another_community_topic(): void
    {
        $admin = $this->admin();
        $otherBrand = $this->otherBrand();
        $foreignTopic = Topic::withoutGlobalScopes()->create([
            'brand_id' => $otherBrand->id,
            'name' => 'Foreign Topic',
            'slug' => 'foreign-topic',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $this->expectException(ModelNotFoundException::class);
        app(CommunityTopicService::class)->delete($foreignTopic->id, $admin);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);

        return $admin;
    }

    private function data(string $name, string $slug): CommunityTopicData
    {
        return new CommunityTopicData($name, null, $slug, 0, true);
    }

    private function otherBrand(): Brand
    {
        return Brand::create([
            'name' => 'Other Topic Community',
            'slug' => 'other-topic-community',
            'domain' => 'other-topic-community.test',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#DCECF7',
            'theme_bg' => '#F8FAFC',
            'registration_mode' => 'invite',
            'is_invite_only' => true,
        ]);
    }
}
