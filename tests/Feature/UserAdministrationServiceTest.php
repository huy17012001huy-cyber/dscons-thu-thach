<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\Auth\UserAdministrationService;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserAdministrationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_ban_and_unban_another_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $target = User::factory()->create();
        Membership::create(['user_id' => $target->id, 'status' => 'active', 'plan' => 'lifetime']);
        $service = app(UserAdministrationService::class);

        self::assertTrue($service->ban($admin, $target));
        self::assertSame('banned', $target->membership->fresh()->status);
        self::assertTrue($service->unban($admin, $target));
        self::assertSame('active', $target->membership->fresh()->status);
        self::assertFalse($service->ban($admin, $admin));
    }

    public function test_non_super_admin_cannot_ban_users(): void
    {
        $this->expectException(AuthorizationException::class);

        app(UserAdministrationService::class)->ban(User::factory()->create(), User::factory()->create());
    }
}
