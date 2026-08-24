<?php

namespace Tests\Feature;

use App\Livewire\Auth\LoginForm;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PasswordLoginModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_login_is_available_when_mode_is_password(): void
    {
        config(['auth.mode' => 'password']);

        $user = User::factory()->create([
            'email' => 'local-login@example.test',
            'password' => Hash::make('password123'),
            'class' => 'offer_architect',
        ]);
        $user->markEmailAsVerified();
        Membership::factory()->active()->create(['user_id' => $user->id]);

        Livewire::test(LoginForm::class)
            ->set('email', 'local-login@example.test')
            ->set('password', 'password123')
            ->call('login');

        $this->assertAuthenticatedAs($user);
    }

    public function test_password_login_does_not_require_membership_for_community_access(): void
    {
        config(['auth.mode' => 'password']);

        $user = User::factory()->create([
            'email' => 'community-only@example.test',
            'password' => Hash::make('password123'),
            'class' => 'offer_architect',
        ]);
        $user->markEmailAsVerified();

        Livewire::test(LoginForm::class)
            ->set('email', 'community-only@example.test')
            ->set('password', 'password123')
            ->call('login');

        $this->assertAuthenticatedAs($user);
    }

    public function test_password_login_action_is_rejected_when_mode_is_google(): void
    {
        config(['auth.mode' => 'google']);

        Livewire::test(LoginForm::class)
            ->set('email', 'local-login@example.test')
            ->set('password', 'password123')
            ->call('login')
            ->assertSet('error', 'Môi trường này chỉ hỗ trợ đăng nhập bằng Google.');

        $this->assertGuest();
    }

    public function test_google_mode_hides_the_password_form(): void
    {
        config(['auth.mode' => 'google']);

        $this->get('/login')
            ->assertOk()
            ->assertSee('Đăng nhập bằng Google')
            ->assertDontSee('id="login-email"', false)
            ->assertDontSee('id="login-password"', false);
    }
}
