<?php

namespace Tests\Feature;

use App\Core\Auth\GoogleAuthService;
use App\Exceptions\GoogleAuthException;
use App\Models\Membership;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_google_user_can_be_created_when_registration_is_open(): void
    {
        brand()->update(['registration_mode' => 'open']);

        $user = app(GoogleAuthService::class)->resolveUser($this->googleUser());

        $this->assertSame('123456789', $user->google_id);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertDatabaseHas('memberships', [
            'user_id' => $user->id,
            'status' => 'active',
            'brand_id' => brand()->id,
        ]);
    }

    public function test_verified_google_email_links_only_to_an_existing_verified_account(): void
    {
        $existing = User::factory()->create(['email' => 'test@example.com']);
        $existing->forceFill(['is_admin' => true])->save();
        Membership::factory()->active()->create(['user_id' => $existing->id]);
        $post = Post::factory()->create(['user_id' => $existing->id, 'content' => '[TEST] Existing history']);
        brand()->users()->attach($existing->id, ['role' => 'owner']);

        $linked = app(GoogleAuthService::class)->resolveUser($this->googleUser());

        $this->assertSame($existing->id, $linked->id);
        $this->assertSame('123456789', $linked->google_id);
        $this->assertTrue($linked->is_admin);
        $this->assertSame(1, User::where('email', 'test@example.com')->count());
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'user_id' => $existing->id]);
        $this->assertDatabaseHas('brand_user', ['brand_id' => brand()->id, 'user_id' => $existing->id, 'role' => 'owner']);
    }

    public function test_unverified_local_email_cannot_be_auto_linked(): void
    {
        $existing = User::factory()->unverified()->create(['email' => 'test@example.com']);
        Membership::factory()->active()->create(['user_id' => $existing->id]);

        $this->expectException(GoogleAuthException::class);

        app(GoogleAuthService::class)->resolveUser($this->googleUser());
    }

    public function test_unverified_google_email_is_rejected(): void
    {
        $this->expectException(GoogleAuthException::class);

        app(GoogleAuthService::class)->resolveUser(
            $this->googleUser(['verified_email' => false])
        );
    }

    public function test_google_provider_without_email_is_rejected(): void
    {
        $this->expectException(GoogleAuthException::class);

        app(GoogleAuthService::class)->resolveUser(
            $this->googleUser(['email' => null])
        );
    }

    public function test_new_google_user_is_blocked_when_registration_is_closed(): void
    {
        $this->expectException(GoogleAuthException::class);

        app(GoogleAuthService::class)->resolveUser(
            $this->googleUser(['email' => 'closed@example.com'])
        );
    }

    public function test_google_redirect_requests_openid_profile_and_email_scopes(): void
    {
        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);
        $provider = Mockery::mock();
        $provider->shouldReceive('scopes')->once()->with(['openid', 'profile', 'email'])->andReturnSelf();
        $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('auth.google.redirect'))
            ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_verified_google_callback_logs_in_a_new_user_and_sends_them_to_onboarding(): void
    {
        brand()->update(['registration_mode' => 'open']);
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($this->googleUser(['email' => 'new-callback@example.com']));
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->withSession(['url.intended' => 'https://evil.example.test'])
            ->get(route('auth.google.callback'))
            ->assertRedirect(route('onboarding'));

        $this->assertAuthenticatedAs(User::where('email', 'new-callback@example.com')->firstOrFail());
    }

    public function test_provider_error_returns_to_login_with_a_safe_message(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andThrow(new \RuntimeException('provider details must not reach UI'));
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Đăng nhập Google không thành công. Vui lòng thử lại hoặc liên hệ Admin.');
    }

    public function test_banned_google_account_is_logged_out_and_redirected_to_login(): void
    {
        $user = User::factory()->create(['google_id' => '123456789']);
        Membership::factory()->banned()->create(['user_id' => $user->id]);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($this->googleUser());
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_expired_google_account_is_logged_out_and_redirected_to_login(): void
    {
        $user = User::factory()->create(['google_id' => '123456789']);
        Membership::factory()->expired()->create(['user_id' => $user->id]);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($this->googleUser());
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    private function googleUser(array $attributes = []): GoogleUser
    {
        return GoogleUser::fake(array_merge([
            'id' => '123456789',
            'name' => 'Google Test User',
            'email' => 'test@example.com',
            'verified_email' => true,
        ], $attributes));
    }
}
