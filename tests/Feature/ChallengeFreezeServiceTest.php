<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\CommunityContext;
use App\Livewire\AdminChallenges;
use App\Models\Brand;
use App\Models\Expedition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Learning\Application\ChallengeFreezeService;
use Tests\TestCase;

final class ChallengeFreezeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_owner_can_freeze_and_resume_its_challenge(): void
    {
        $owner = User::factory()->create();
        $owner->brandRoles()->attach(brand()->id, ['role' => 'owner']);
        $challenge = $this->challenge($owner);
        $service = app(ChallengeFreezeService::class);

        $frozen = $service->freeze($challenge, $owner, 3, now()->addDays(2));

        self::assertNotNull($frozen);
        self::assertSame(3, $frozen->freeze_from_day);
        self::assertNotNull($frozen->freeze_ends_at);
        self::assertNotNull($service->clear($challenge, $owner));
        self::assertNull($challenge->fresh()->freeze_from_day);
    }

    public function test_freeze_service_rejects_a_challenge_from_another_community(): void
    {
        $owner = User::factory()->create();
        $owner->brandRoles()->attach(brand()->id, ['role' => 'owner']);
        $challenge = $this->challenge($owner);
        $other = Brand::create([
            'name' => 'Other community',
            'slug' => 'other-community',
            'domain' => 'other-community.test',
            'status' => 'active',
            'theme_primary' => '#1F77BE',
            'theme_accent' => '#E1F4F7',
            'theme_bg' => '#F7FAFC',
        ]);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        app(CommunityContext::class)->run($other, function () use ($challenge, $owner): void {
            app(ChallengeFreezeService::class)->freeze($challenge, $owner, 3, now()->addDays(2));
        });
    }

    public function test_admin_challenges_uses_the_freeze_service(): void
    {
        $owner = User::factory()->create();
        $owner->brandRoles()->attach(brand()->id, ['role' => 'owner']);
        $challenge = $this->challenge($owner);

        Livewire::actingAs($owner)
            ->test(AdminChallenges::class)
            ->set('freezingExpeditionId', $challenge->id)
            ->set('freezeFromDay', 2)
            ->set('freezeUntil', now('Asia/Ho_Chi_Minh')->addDay()->format('Y-m-d\TH:i'))
            ->call('saveFreeze')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('expeditions', ['id' => $challenge->id, 'freeze_from_day' => 2]);
    }

    private function challenge(User $owner): Expedition
    {
        return Expedition::create([
            'title' => 'Freeze service challenge',
            'slug' => 'freeze-service-challenge',
            'description' => 'Challenge freeze service test.',
            'difficulty' => 'normal',
            'required_days' => 7,
            'max_members' => 50,
            'created_by' => $owner->id,
            'leader_id' => $owner->id,
            'status' => 'active',
        ]);
    }
}
