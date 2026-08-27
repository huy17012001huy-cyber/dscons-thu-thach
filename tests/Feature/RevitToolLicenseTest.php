<?php

namespace Tests\Feature;

use App\Models\DigitalProduct;
use App\Models\ProductPurchase;
use App\Models\ToolInstallation;
use App\Models\ToolSecurityEvent;
use App\Models\ToolSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevitToolLicenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_revit_purchase_is_returned_only_to_the_authorized_device(): void
    {
        $user = User::factory()->create();
        $tool = $this->tool('dscons-test-1');
        ProductPurchase::create([
            'brand_id' => brand()->id,
            'user_id' => $user->id,
            'digital_product_id' => $tool->id,
            'status' => 'active',
            'amount_paid' => 0,
            'paid_at' => now(),
        ]);

        $token = $this->authorize($user, $this->device('one'));

        $this->withToken($token)->getJson('/api/revit/entitlements')
            ->assertOk()
            ->assertJsonPath('tools.0.tool_key', 'dscons-test-1');
        $this->withToken($token)->getJson('/api/revit/tools/dscons-test-1/manifest')
            ->assertOk()
            ->assertJsonPath('embedded', true);
        $this->withToken($token)->getJson('/api/v1/revit/entitlements')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tools.0.tool_key', 'dscons-test-1');
    }

    public function test_device_authorization_can_be_polled_only_once(): void
    {
        $user = User::factory()->create();
        [, $code, $browserCode] = $this->start($this->device('one'));
        $this->postJson('/api/revit/device/poll', ['authorization_code' => $browserCode])
            ->assertStatus(410);
        $this->actingAs($user)->post(route('revit.authorization.approve', $browserCode))->assertRedirect();

        $first = $this->postJson('/api/revit/device/poll', ['authorization_code' => $code])
            ->assertOk()
            ->assertJsonPath('status', 'approved');
        $this->assertNotEmpty($first->json('access_token'));
        $this->postJson('/api/revit/device/poll', ['authorization_code' => $code])
            ->assertForbidden();
    }

    public function test_second_conflict_locks_new_activation_for_twenty_four_hours_and_third_locks_license(): void
    {
        $user = User::factory()->create();
        $this->authorize($user, $this->device('one'));

        [, $secondCode, $secondBrowserCode] = $this->start($this->device('two'));
        $this->actingAs($user)->post(route('revit.authorization.approve', $secondBrowserCode))
            ->assertRedirect()
            ->assertSessionHas('error');

        [, $thirdCode, $thirdBrowserCode] = $this->start($this->device('three'));
        $this->actingAs($user)->post(route('revit.authorization.approve', $thirdBrowserCode))
            ->assertRedirect()
            ->assertSessionHas('error');

        $installation = ToolInstallation::withoutGlobalScopes()->firstOrFail();
        $this->assertTrue($installation->blocked_until->isFuture());
        $this->assertSame(2, ToolSecurityEvent::withoutGlobalScopes()->where('event_type', 'activation_conflict')->count());

        $this->travel(25)->hours();
        [, $fourthCode, $fourthBrowserCode] = $this->start($this->device('four'));
        $this->actingAs($user)->post(route('revit.authorization.approve', $fourthBrowserCode))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('blocked', $installation->fresh()->status);
        $this->assertSame(3, ToolSecurityEvent::withoutGlobalScopes()->where('event_type', 'activation_conflict')->count());
        $this->assertNotNull(ToolSession::query()->where('tool_installation_id', $installation->id)->firstOrFail()->revoked_at);
    }

    public function test_heartbeat_updates_runtime_data_and_logout_revokes_the_token(): void
    {
        $user = User::factory()->create();
        $token = $this->authorize($user, $this->device('one'));

        $this->withToken($token)->postJson('/api/revit/heartbeat', ['revit_version' => '2024', 'client_version' => '0.1.0'])
            ->assertOk()
            ->assertJsonPath('next_check_after_hours', 24);
        $installation = ToolInstallation::withoutGlobalScopes()->firstOrFail();
        $this->assertSame('2024', $installation->last_revit_version);

        $this->withToken($token)->postJson('/api/revit/logout')->assertOk();
        $this->withToken($token)->getJson('/api/revit/entitlements')->assertUnauthorized();
        $this->assertNotNull(ToolSession::withoutGlobalScopes()->firstOrFail()->revoked_at);
        $this->assertDatabaseHas('audit_logs', [
            'brand_id' => brand()->id,
            'actor_id' => $user->id,
            'domain' => 'revit_tools',
            'action' => 'user_logout',
            'subject_type' => ToolSecurityEvent::class,
        ]);
    }

    public function test_v1_revit_api_uses_the_standard_response_contract_without_changing_legacy_routes(): void
    {
        $user = User::factory()->create();
        $tool = $this->tool('dscons-v1-tool');
        ProductPurchase::create([
            'brand_id' => brand()->id,
            'user_id' => $user->id,
            'digital_product_id' => $tool->id,
            'status' => 'active',
            'amount_paid' => 0,
            'paid_at' => now(),
        ]);
        $device = $this->device('v1');
        $start = $this->postJson('/api/v1/revit/device/start', $device)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 201);
        $code = $start->json('data.authorization_code');
        $browserCode = basename(parse_url($start->json('data.verification_url'), PHP_URL_PATH));

        $this->actingAs($user)->post(route('revit.authorization.approve', $browserCode))->assertRedirect();
        $poll = $this->postJson('/api/v1/revit/device/poll', ['authorization_code' => $code])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'approved');
        $token = $poll->json('data.access_token');

        $this->withToken($token)->getJson('/api/v1/revit/tools/dscons-v1-tool/manifest')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tool_key', 'dscons-v1-tool');
        $this->withToken($token)->postJson('/api/v1/revit/heartbeat', ['revit_version' => '2024'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'active');
        $this->withToken($token)->postJson('/api/v1/revit/logout')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'logged_out');

        $this->postJson('/api/revit/device/start', $this->device('legacy'))->assertCreated();
    }

    private function tool(string $key): DigitalProduct
    {
        return DigitalProduct::withoutGlobalScopes()->create([
            'brand_id' => brand()->id,
            'title' => 'Tool '.$key,
            'price' => 0,
            'delivery_type' => 'link',
            'is_published' => true,
            'product_kind' => 'revit_tool',
            'tool_key' => $key,
            'supported_revit_versions' => ['2024'],
            'tool_manifest_version' => '1.0.0',
            'is_license_required' => true,
        ]);
    }

    /** @return array{0: array<string, string>, 1: string, 2: string} */
    private function start(array $device): array
    {
        $response = $this->postJson('/api/revit/device/start', $device)->assertCreated();

        return [$device, $response->json('authorization_code'), basename(parse_url($response->json('verification_url'), PHP_URL_PATH))];
    }

    private function authorize(User $user, array $device): string
    {
        [, $code, $browserCode] = $this->start($device);
        $this->actingAs($user)->post(route('revit.authorization.approve', $browserCode))->assertRedirect();

        return $this->postJson('/api/revit/device/poll', ['authorization_code' => $code])
            ->assertOk()
            ->json('access_token');
    }

    /** @return array<string, string> */
    private function device(string $suffix): array
    {
        return [
            'installation_id' => str_repeat($suffix, 32),
            'device_fingerprint' => str_repeat($suffix, 32),
            'device_label' => 'DESKTOP-'.$suffix,
            'revit_version' => '2024',
            'client_version' => '0.1.0',
        ];
    }
}
