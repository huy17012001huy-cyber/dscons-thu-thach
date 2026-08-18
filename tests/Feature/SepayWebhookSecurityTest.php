<?php

namespace Tests\Feature;

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
}
