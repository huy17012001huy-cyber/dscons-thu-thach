<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\Auth\AdminTwoFactorService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FA\Google2FA;
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

    public function test_super_admin_can_confirm_totp_and_a_recovery_code_can_only_be_used_once(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $service = app(AdminTwoFactorService::class);
        $setup = $service->prepare($admin);
        $code = app(Google2FA::class)->getCurrentOtp($setup['secret']);

        $this->assertTrue($service->confirm($admin, $setup['secret'], $code, $setup['recovery_codes']));
        $this->assertTrue($service->verify($admin->fresh(), $code));

        $recoveryCode = $setup['recovery_codes'][0];
        $this->assertTrue($service->useRecoveryCode($admin->fresh(), $recoveryCode));
        $this->assertFalse($service->useRecoveryCode($admin->fresh(), $recoveryCode));
        $this->assertNotSame($recoveryCode, $admin->fresh()->two_factor_recovery_codes[0]);
    }

    public function test_super_admin_can_revoke_other_database_sessions(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        DB::table('sessions')->insert([
            'id' => 'other-admin-session',
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.2',
            'user_agent' => 'Test browser',
            'payload' => 'test',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.security.sessions.revoke'))
            ->assertRedirect();

        $this->assertDatabaseMissing('sessions', ['id' => 'other-admin-session']);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'domain' => 'auth',
            'action' => 'admin_sessions_revoked',
        ]);
    }
}
