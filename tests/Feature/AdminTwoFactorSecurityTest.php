<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminTwoFactorSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_must_reverify_before_impersonating_when_two_factor_is_enforced(): void
    {
        config(['admin-security.two_factor_enforced' => true]);
        $admin = User::factory()->create(['is_admin' => true]);
        $admin->forceFill([
            'two_factor_secret' => 'encrypted-test-secret',
            'two_factor_recovery_codes' => [],
            'two_factor_confirmed_at' => now(),
        ])->save();
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.impersonate.start', $target))
            ->assertRedirect(route('admin.two-factor.verify'));
    }

    public function test_two_factor_secret_is_not_exposed_by_the_user_model_serialization(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $admin->forceFill([
            'two_factor_secret' => 'encrypted-test-secret',
            'two_factor_recovery_codes' => [],
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->assertArrayNotHasKey('two_factor_secret', $admin->fresh()->toArray());
        $this->assertArrayNotHasKey('two_factor_recovery_codes', $admin->fresh()->toArray());
    }
}
