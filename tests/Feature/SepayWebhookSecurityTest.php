<?php

namespace Tests\Feature;

use App\Models\CommerceWebhookEvent;
use App\Models\Expedition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SepayWebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_requires_authorization_header(): void
    {
        $response = $this->postJson(route('webhook.sepay'), [
            'transferType' => 'in',
            'content' => 'TEST',
            'transferAmount' => 1000,
        ]);

        $response->assertStatus(401);
    }

    public function test_webhook_rejects_invalid_api_key(): void
    {
        config(['services.sepay.webhook_token' => 'secret-token']);

        $response = $this->postJson(route('webhook.sepay'), [
            'transferType' => 'in',
            'content' => 'TEST',
            'transferAmount' => 1000,
        ], [
            'Authorization' => 'Apikey wrong-token',
        ]);

        $response->assertStatus(401);
    }

    public function test_webhook_accepts_valid_api_key(): void
    {
        config(['services.sepay.webhook_token' => 'secret-token']);

        $response = $this->postJson(route('webhook.sepay'), [
            'transferType' => 'in',
            'content' => 'TEST',
            'transferAmount' => 1000,
        ], [
            'Authorization' => 'Apikey secret-token',
        ]);

        $response->assertStatus(200);
    }

    public function test_webhook_validation_returns_the_standard_json_error_shape(): void
    {
        config(['services.sepay.webhook_token' => 'secret-token']);

        $response = $this->postJson(route('webhook.sepay'), [
            'transferType' => 'in',
            'transferAmount' => -1,
        ], [
            'Authorization' => 'Apikey secret-token',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 422)
            ->assertJsonStructure(['message', 'errors' => ['transferAmount']]);
    }

    public function test_webhook_event_is_idempotent_by_provider_event_id(): void
    {
        config(['services.sepay.webhook_token' => 'secret-token']);
        $payload = [
            'id' => 'sepay-event-1001',
            'transferType' => 'in',
            'content' => 'UNMATCHED',
            'transferAmount' => 1000,
        ];

        $this->postJson(route('webhook.sepay'), $payload, ['Authorization' => 'Apikey secret-token'])->assertOk();
        $this->postJson(route('webhook.sepay'), $payload, ['Authorization' => 'Apikey secret-token'])->assertOk();

        $this->assertSame(1, CommerceWebhookEvent::query()->where('provider', 'sepay')->count());
        $this->assertSame('ignored', CommerceWebhookEvent::query()->value('status'));
    }

    public function test_v1_webhook_uses_the_standard_response_contract_without_changing_the_legacy_endpoint(): void
    {
        config(['services.sepay.webhook_token' => 'secret-token']);

        $this->postJson('/api/v1/webhooks/sepay', [
            'id' => 'sepay-v1-event-1001',
            'transferType' => 'in',
            'content' => 'UNMATCHED',
            'transferAmount' => 1000,
        ], ['Authorization' => 'Apikey secret-token'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.accepted', true);
        $this->postJson(route('webhook.sepay'), [
            'transferType' => 'in',
            'content' => 'TEST',
            'transferAmount' => 1000,
        ], ['Authorization' => 'Apikey secret-token'])
            ->assertOk()
            ->assertExactJson(['success' => true]);
    }

    public function test_bot_api_requires_authorization_header(): void
    {
        $response = $this->getJson('/api/bot/member?q=test');

        $response->assertStatus(401);
    }

    public function test_bot_api_rejects_invalid_token(): void
    {
        config(['services.bot.api_token' => 'bot-token']);

        $response = $this->getJson('/api/bot/member?q=test', [
            'Authorization' => 'Bearer wrong-token',
        ]);

        $response->assertStatus(401);
    }

    public function test_bot_api_accepts_valid_token(): void
    {
        config(['services.bot.api_token' => 'bot-token']);

        // We don't need to mock the user for this auth check test if we just want to see it passes verify()
        // but BotApiController::lookupMember will fail with 400 if q is missing or 404 if user not found.
        // We just want to check it's NOT 401.

        $response = $this->getJson('/api/bot/member?q=nonexistent', [
            'Authorization' => 'Bearer bot-token',
        ]);

        $this->assertNotEquals(401, $response->getStatusCode());
    }

    public function test_bot_api_keeps_the_legacy_missing_query_response(): void
    {
        config(['services.bot.api_token' => 'bot-token']);

        $response = $this->getJson('/api/bot/member', [
            'Authorization' => 'Bearer bot-token',
        ]);

        $response
            ->assertStatus(400)
            ->assertExactJson(['error' => 'Missing q parameter']);
    }

    public function test_v1_bot_api_uses_standard_contract_for_member_and_challenge_queries(): void
    {
        config(['services.bot.api_token' => 'bot-token']);
        $user = User::factory()->create(['username' => 'bot-member']);
        $challenge = Expedition::create([
            'title' => 'Bot Challenge',
            'slug' => 'bot-challenge',
            'difficulty' => 'normal',
            'required_days' => 7,
            'max_members' => 20,
            'created_by' => $user->id,
            'leader_id' => $user->id,
            'status' => 'active',
        ]);

        $this->getJson('/api/v1/bot/member?q=bot-member', ['Authorization' => 'Bearer bot-token'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.username', 'bot-member');
        $this->getJson('/api/v1/bot/challenge-progress?q=bot-member&challenge='.$challenge->slug, ['Authorization' => 'Bearer bot-token'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'not_enrolled');
        $this->getJson('/api/v1/bot/pending-submissions?challenge='.$challenge->slug, ['Authorization' => 'Bearer bot-token'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.pending_count', 0);
    }
}
