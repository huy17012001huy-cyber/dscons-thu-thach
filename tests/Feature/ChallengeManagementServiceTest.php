<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\CommunityContext;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Learning\Application\ChallengeManagementService;
use Tests\TestCase;

final class ChallengeManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_admin_can_create_update_and_delete_a_challenge(): void
    {
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);
        $service = app(ChallengeManagementService::class);

        $challenge = $service->save(null, $admin, $this->attributes('Created Challenge'));

        self::assertNotNull($challenge);
        self::assertSame(brand()->id, $challenge->brand_id);
        self::assertSame($admin->id, $challenge->created_by);
        $updated = $service->save($challenge, $admin, [...$this->attributes('Updated Challenge'), 'required_days' => 14]);
        self::assertSame('Updated Challenge', $updated?->title);
        self::assertSame(14, $updated?->required_days);
        self::assertTrue($service->delete($challenge->fresh(), $admin));
        $this->assertDatabaseMissing('expeditions', ['id' => $challenge->id]);
    }

    public function test_challenge_management_rejects_cross_community_writes(): void
    {
        $admin = User::factory()->create();
        $admin->brandRoles()->attach(brand()->id, ['role' => 'admin']);
        $challenge = app(ChallengeManagementService::class)->save(null, $admin, $this->attributes('Scoped Challenge'));
        self::assertNotNull($challenge);
        $other = Brand::create([
            'name' => 'Other community',
            'slug' => 'other-community',
            'domain' => 'other-community.test',
            'status' => 'active',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#E1F4F7',
            'theme_bg' => '#F7FAFC',
        ]);

        app(CommunityContext::class)->run($other, function () use ($admin, $challenge): void {
            self::assertNull(
                app(ChallengeManagementService::class)->save($challenge, $admin, $this->attributes('Cross community write')),
            );
        });
        self::assertSame('Scoped Challenge', $challenge->fresh()->title);
    }

    /** @return array<string, mixed> */
    private function attributes(string $title): array
    {
        return [
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'description' => 'Challenge management service test.',
            'boss_name' => 'Challenge owner',
            'leader_id' => User::factory()->create()->id,
            'difficulty' => 'normal',
            'required_days' => 7,
            'max_members' => 50,
            'status' => 'open',
            'price' => 0,
        ];
    }
}
