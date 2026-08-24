<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test active member can access protected route
     */
    public function test_active_member_passes_middleware(): void
    {
        $user = User::factory()->create(['class' => 'offer_architect']);
        Membership::factory()->active()->create(['user_id' => $user->id]);
        $user->brandRoles()->attach(brand()->id, ['role' => 'member']);

        $response = $this->actingAs($user)->get('/feed');

        $response->assertStatus(200);
    }

    /**
     * Test trial member can access protected route
     */
    public function test_trial_member_passes_middleware(): void
    {
        $user = User::factory()->create(['class' => 'traffic_mage']);
        Membership::factory()->create(['user_id' => $user->id, 'status' => 'trial', 'trial_ends_at' => now()->addDays(7)]);
        $user->brandRoles()->attach(brand()->id, ['role' => 'member']);

        $response = $this->actingAs($user)->get('/feed');

        $response->assertStatus(200);
    }

    /**
     * Test expired trial redirects to membership.expired
     */
    public function test_nonparticipant_with_expired_trial_is_sent_to_community_join(): void
    {
        $user = User::factory()->create(['class' => 'conversion_ranger']);
        Membership::factory()->expiredTrial()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/feed');

        $response->assertRedirectToRoute('community.preview', ['community' => 'dscons']);
    }

    /**
     * Test expired active membership redirects to membership.expired
     */
    public function test_nonparticipant_with_expired_membership_is_sent_to_community_join(): void
    {
        $user = User::factory()->create(['class' => 'delivery_assassin']);
        Membership::factory()->expired()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/feed');

        $response->assertRedirectToRoute('community.preview', ['community' => 'dscons']);
    }

    /**
     * Test banned user gets logged out
     */
    public function test_banned_membership_does_not_block_community_join(): void
    {
        $user = User::factory()->create(['class' => 'continuity_captain']);
        Membership::factory()->banned()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/feed');

        $response->assertRedirectToRoute('community.preview', ['community' => 'dscons']);
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test no membership logs out and redirects to login
     */
    public function test_nonparticipant_without_membership_is_sent_to_community_join(): void
    {
        $user = User::factory()->create(['class' => 'offer_architect']);
        // Don't create any membership

        $response = $this->actingAs($user)->get('/feed');

        // A non-participant is invited to join the community. Premium is not
        // required for community access, but joining the community is.
        $response->assertRedirectToRoute('community.preview', ['community' => 'dscons']);
        $this->assertAuthenticatedAs($user);
    }

    public function test_community_member_can_access_feed_without_premium_membership(): void
    {
        $user = User::factory()->create(['class' => 'offer_architect']);
        $user->brandRoles()->attach(brand()->id, ['role' => 'member']);

        $response = $this->actingAs($user)->get('/feed');

        $response->assertOk();
    }

    /**
     * Test user without class redirects to onboarding if level >= 10
     */
    public function test_user_without_class_redirects_to_onboarding(): void
    {
        $user = User::factory()->create(['class' => null, 'level' => 10]);
        Membership::factory()->active()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/feed');

        $response->assertRedirectToRoute('onboarding');
    }

    /**
     * Test different protected routes all check membership
     */
    public function test_community_routes_require_participation_not_membership(): void
    {
        $user = User::factory()->create(['class' => 'offer_architect']);
        Membership::factory()->expired()->create(['user_id' => $user->id]);

        $routes = [
            '/feed',
            '/cot',
            '/tin-hieu',
            '/hoi-dap',
            '/challenge',
            '/leaderboard',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($user)->get($route);
            $response->assertRedirectToRoute('community.preview', ['community' => 'dscons']);
        }
    }

    /**
     * Test membership status 'active' with future expiration passes
     */
    public function test_active_with_future_expiration(): void
    {
        $user = User::factory()->create(['class' => 'traffic_mage']);
        Membership::factory()
            ->create([
                'user_id' => $user->id,
                'status' => 'active',
                'expires_at' => now()->addMonths(1),
            ]);
        $user->brandRoles()->attach(brand()->id, ['role' => 'member']);

        $response = $this->actingAs($user)->get('/feed');

        $response->assertStatus(200);
    }

    /**
     * Test membership status 'active' with past expiration redirects
     */
    public function test_nonparticipant_with_past_membership_is_sent_to_community_join(): void
    {
        $user = User::factory()->create(['class' => 'conversion_ranger']);
        Membership::factory()
            ->create([
                'user_id' => $user->id,
                'status' => 'active',
                'expires_at' => now()->subDay(),
            ]);

        $response = $this->actingAs($user)->get('/feed');

        $response->assertRedirectToRoute('community.preview', ['community' => 'dscons']);
    }

    /**
     * Test banned status always logs out regardless of expiration
     */
    public function test_banned_status_does_not_block_community_join(): void
    {
        $user = User::factory()->create(['class' => 'delivery_assassin']);
        Membership::factory()
            ->create([
                'user_id' => $user->id,
                'status' => 'banned',
                'expires_at' => now()->addYear(),
            ]);

        $response = $this->actingAs($user)->get('/feed');

        $response->assertRedirectToRoute('community.preview', ['community' => 'dscons']);
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test membership expired page accessible
     */
    public function test_membership_expired_page_accessible(): void
    {
        $user = User::factory()->create(['class' => 'continuity_captain']);

        $response = $this->actingAs($user)->get('/membership/expired');

        $response->assertStatus(200);
    }

    /**
     * Test profile page requires active membership
     */
    public function test_profile_page_allows_community_member_without_active_membership(): void
    {
        $user = User::factory()->create(['class' => 'offer_architect', 'username' => 'testuser']);
        Membership::factory()->expired()->create(['user_id' => $user->id]);
        $user->brandRoles()->attach(brand()->id, ['role' => 'member']);

        $response = $this->actingAs($user)->get('/@testuser');

        $response->assertOk();
    }

    /**
     * Test multiple users with different memberships
     */
    public function test_multiple_users_different_statuses(): void
    {
        $activeUser = User::factory()->create(['class' => 'offer_architect']);
        Membership::factory()->active()->create(['user_id' => $activeUser->id]);
        $activeUser->brandRoles()->attach(brand()->id, ['role' => 'member']);

        $expiredUser = User::factory()->create(['class' => 'traffic_mage']);
        Membership::factory()->expired()->create(['user_id' => $expiredUser->id]);

        $bannedUser = User::factory()->create(['class' => 'conversion_ranger']);
        Membership::factory()->banned()->create(['user_id' => $bannedUser->id]);

        // Active passes
        $response = $this->actingAs($activeUser)->get('/feed');
        $response->assertStatus(200);

        // Expired redirects
        $response = $this->actingAs($expiredUser)->get('/feed');
        $response->assertRedirectToRoute('community.preview', ['community' => 'dscons']);

        // Banned logs out
        $response = $this->actingAs($bannedUser)->get('/feed');
        $response->assertRedirectToRoute('community.preview', ['community' => 'dscons']);
    }
}
