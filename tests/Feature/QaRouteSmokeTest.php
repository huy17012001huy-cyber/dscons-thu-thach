<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class QaRouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{0: string}> */
    public static function protectedRoutes(): array
    {
        return [
            'feed' => ['/feed'],
            'cot' => ['/cot'],
            'signals' => ['/tin-hieu'],
            'qa' => ['/hoi-dap'],
            'challenge' => ['/challenge'],
            'leaderboard' => ['/leaderboard'],
            'academy' => ['/khoa-hoc'],
            'events' => ['/su-kien'],
            'marketplace' => ['/marketplace'],
            'affiliate' => ['/affiliate'],
            'messages' => ['/messages'],
            'search' => ['/search'],
            'admin' => ['/admin'],
        ];
    }

    #[DataProvider('protectedRoutes')]
    public function test_guest_is_redirected_to_google_login_for_protected_routes(string $path): void
    {
        $this->get($path)
            ->assertRedirect(route('login'));
    }

    public function test_public_auth_compatibility_urls_redirect_to_google_login(): void
    {
        $this->get('/register')->assertRedirect(route('login'));
        $this->get('/quen-mat-khau')->assertRedirect(route('login'));
        $this->get('/dat-lai-mat-khau/test-token')->assertRedirect(route('login'));
    }

    public function test_public_password_login_post_is_not_available(): void
    {
        $this->post('/login', [
            'email' => 'legacy@example.test',
            'password' => 'legacy-password',
        ])->assertStatus(405);
    }

    public function test_dscons_does_not_expose_legacy_business_guides(): void
    {
        $user = User::factory()->create(['class' => 'offer_architect']);
        Membership::factory()->active()->create(['user_id' => $user->id]);
        $user->brandRoles()->attach(brand()->id, ['role' => 'member']);

        $this->get('/sale-like-crazy')->assertNotFound();
        $this->get('/iman-funnel-map-viet.html')->assertNotFound();
        $this->actingAs($user)->get('/blog')->assertNotFound();
        $this->actingAs($user)->get('/bao-cao-huong-dan-quan-tri-menu.html')->assertNotFound();
    }
}
