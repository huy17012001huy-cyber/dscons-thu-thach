<?php

namespace Tests\Feature;

use App\Livewire\AdminUsers;
use App\Mail\WelcomeMemberMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class GoogleOnlyProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_webhook_creates_a_google_only_account_without_password(): void
    {
        config(['services.register_webhook.secret' => 'test-secret']);
        Mail::fake();

        $response = $this->postJson(route('webhook.register'), [
            'secret' => 'test-secret',
            'name' => '[TEST] Webhook Member',
            'email' => 'webhook-member@example.test',
        ]);

        $response->assertCreated()->assertJsonMissingPath('password');
        $user = User::where('email', 'webhook-member@example.test')->firstOrFail();
        $this->assertNull($user->password);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertSame('member', $user->communityRole(brand()->id));
        Mail::assertSent(WelcomeMemberMail::class, function (WelcomeMemberMail $mail): bool {
            $body = $mail->render();

            return str_contains($body, 'ĐĂNG NHẬP BẰNG GOOGLE')
                && ! str_contains($body, 'Mật khẩu')
                && ! str_contains($body, 'Đặt lại mật khẩu');
        });
    }

    public function test_admin_provisioning_creates_a_google_only_account(): void
    {
        Mail::fake();
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        Livewire::actingAs($admin)
            ->test(AdminUsers::class)
            ->call('openCreateModal')
            ->set('newName', '[TEST] Admin Member')
            ->set('newEmail', 'admin-member@example.test')
            ->set('newRole', 'member')
            ->call('createUser')
            ->assertHasNoErrors();

        $user = User::where('email', 'admin-member@example.test')->firstOrFail();
        $this->assertNull($user->password);
        $this->assertTrue($user->hasVerifiedEmail());
        Mail::assertSent(WelcomeMemberMail::class);
    }

    public function test_register_webhook_validation_returns_a_json_error_contract(): void
    {
        config(['services.register_webhook.secret' => 'test-secret']);

        $response = $this->postJson(route('webhook.register'), [
            'secret' => 'test-secret',
            'name' => 'A',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 422)
            ->assertJsonStructure(['message', 'errors' => ['email']]);
    }
}
