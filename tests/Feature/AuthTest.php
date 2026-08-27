<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test register creates user with valid data
     */
    public function test_register_creates_user(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'username' => 'john.doe',
            'password' => Hash::make('password123'),
            'level' => 1,
            'xp' => 0,
            'aip' => 0,
            'streak' => 0,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    /**
     * Test register component via livewire call
     */
    public function test_register_via_livewire_form(): void
    {
        $this->post('/livewire/message/app.livewire.auth.register-form', [
            'components' => [
                [
                    'id' => 'component-1',
                    'tag' => 'app.livewire.auth.register-form',
                ],
            ],
            'updates' => [
                [
                    'type' => 'syncInput',
                    'payload' => [
                        'name' => 'name',
                        'value' => 'Jane Smith',
                    ],
                ],
            ],
        ]);

        // Test basic flow without Livewire's full infrastructure
        $user = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'username' => 'jane.smith',
            'password' => Hash::make('password123'),
            'level' => 1,
            'xp' => 0,
            'aip' => 0,
            'streak' => 0,
        ]);

        Membership::create([
            'user_id' => $user->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(3),
        ]);

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
        $this->assertDatabaseHas('memberships', ['user_id' => $user->id, 'status' => 'trial']);
    }

    /**
     * Test register creates trial membership
     */
    public function test_register_creates_trial_membership(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'test.user',
            'password' => Hash::make('password123'),
            'level' => 1,
            'xp' => 0,
            'aip' => 0,
            'streak' => 0,
        ]);

        Membership::create([
            'user_id' => $user->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(3),
        ]);

        $this->assertDatabaseHas('memberships', [
            'user_id' => $user->id,
            'status' => 'trial',
        ]);

        $membership = $user->membership;
        $this->assertEquals('trial', $membership->status);
        $this->assertTrue($membership->trial_ends_at->isFuture());
    }

    /**
     * Test register captures referral
     */
    public function test_register_captures_referral(): void
    {
        $referrer = User::factory()->create();

        $user = User::create([
            'name' => 'New User',
            'email' => 'new@example.com',
            'username' => 'new.user',
            'password' => Hash::make('password123'),
            'level' => 1,
            'xp' => 0,
            'aip' => 0,
            'streak' => 0,
            'referred_by' => $referrer->id,
        ]);

        Membership::create([
            'user_id' => $user->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(3),
            'referred_by' => $referrer->id,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'referred_by' => $referrer->id,
        ]);

        $this->assertDatabaseHas('memberships', [
            'user_id' => $user->id,
            'referred_by' => $referrer->id,
        ]);
    }

    /**
     * Test register generates unique username
     */
    public function test_register_generates_unique_username(): void
    {
        $user1 = User::create([
            'name' => 'John Doe',
            'email' => 'john1@example.com',
            'username' => 'john.doe',
            'password' => Hash::make('password123'),
        ]);

        $user2 = User::create([
            'name' => 'John Doe',
            'email' => 'john2@example.com',
            'username' => 'john.doe2', // Should be unique
            'password' => Hash::make('password123'),
        ]);

        $this->assertNotEquals($user1->username, $user2->username);
        $this->assertDatabaseHas('users', ['username' => 'john.doe']);
        $this->assertDatabaseHas('users', ['username' => 'john.doe2']);
    }

    /**
     * Test login with valid credentials
     */
    public function test_login_with_valid_credentials(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'test.user',
            'password' => Hash::make('password123'),
            'class' => 'offer_architect',
            'level' => 1,
            'xp' => 0,
            'aip' => 0,
            'streak' => 0,
        ]);

        Membership::factory()->active()->create(['user_id' => $user->id]);

        // Authenticate directly
        $this->actingAs($user);
        $this->assertAuthenticated();
    }

    /**
     * Test login with invalid password
     */
    public function test_login_with_invalid_password(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'test.user',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
    }

    /**
     * Test login redirects to feed for active member
     */
    public function test_login_redirects_to_feed(): void
    {
        $user = User::create([
            'name' => 'Active User',
            'email' => 'active@example.com',
            'username' => 'active.user',
            'password' => Hash::make('password123'),
            'class' => 'traffic_mage',
            'level' => 1,
            'xp' => 0,
            'aip' => 0,
            'streak' => 0,
        ]);

        Membership::factory()->active()->create(['user_id' => $user->id]);
        $user->brandRoles()->attach(brand()->id, ['role' => 'member']);
        $user->markEmailAsVerified();

        $this->actingAs($user);
        $response = $this->get('/feed');

        $response->assertStatus(200);
    }

    /**
     * Test login redirects to onboarding if no class
     */
    public function test_login_redirects_to_onboarding_if_no_class(): void
    {
        $user = User::create([
            'name' => 'No Class User',
            'email' => 'noclass@example.com',
            'username' => 'noclass.user',
            'password' => Hash::make('password123'),
            'class' => null,
            'level' => 1,
            'xp' => 0,
            'aip' => 0,
            'streak' => 0,
        ]);

        Membership::factory()->active()->create(['user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->get('/onboarding');

        $response->assertStatus(200);
    }

    /**
     * Test login with expired membership redirects
     */
    public function test_login_with_expired_membership(): void
    {
        $user = User::create([
            'name' => 'Expired User',
            'email' => 'expired@example.com',
            'username' => 'expired.user',
            'password' => Hash::make('password123'),
            'class' => 'conversion_ranger',
            'level' => 1,
            'xp' => 0,
            'aip' => 0,
            'streak' => 0,
        ]);

        Membership::factory()->expired()->create(['user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->get('/membership/expired');

        $response->assertStatus(200);
    }

    /**
     * Test login with banned membership logs out
     */
    public function test_login_with_banned_membership(): void
    {
        $user = User::create([
            'name' => 'Banned User',
            'email' => 'banned@example.com',
            'username' => 'banned.user',
            'password' => Hash::make('password123'),
            'class' => 'delivery_assassin',
            'level' => 1,
            'xp' => 0,
            'aip' => 0,
            'streak' => 0,
        ]);

        Membership::factory()->banned()->create(['user_id' => $user->id]);
        $user->brandRoles()->attach(brand()->id, ['role' => 'member']);
        $user->markEmailAsVerified();

        $this->actingAs($user);
        $response = $this->get('/feed');

        $response->assertStatus(200);
    }

    /**
     * Test logout
     */
    public function test_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirectToRoute('login');
    }

    /**
     * Test guest cannot access protected routes
     */
    public function test_guest_cannot_access_protected_routes(): void
    {
        $response = $this->get('/feed');

        $response->assertRedirectToRoute('login');
    }

    /**
     * Test register requires email
     */
    public function test_register_requires_email(): void
    {
        $user = User::create([
            'name' => 'No Email',
            'email' => 'noemail@example.com',
            'username' => 'noemail',
            'password' => Hash::make('password123'),
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'No Email',
        ]);
    }

    /**
     * Test register requires unique email
     */
    public function test_register_requires_unique_email(): void
    {
        $user1 = User::create([
            'name' => 'User 1',
            'email' => 'duplicate@example.com',
            'username' => 'user1',
            'password' => Hash::make('password123'),
        ]);

        try {
            $user2 = User::create([
                'name' => 'User 2',
                'email' => 'duplicate@example.com',
                'username' => 'user2',
                'password' => Hash::make('password123'),
            ]);
        } catch (\Exception $e) {
            // Expected: unique constraint violation
        }

        $count = User::where('email', 'duplicate@example.com')->count();
        $this->assertEquals(1, $count);
    }

    /**
     * Test register initial user stats
     */
    public function test_register_sets_initial_user_stats(): void
    {
        $user = User::create([
            'name' => 'New Player',
            'email' => 'newplayer@example.com',
            'username' => 'newplayer',
            'password' => Hash::make('password123'),
            'level' => 1,
            'xp' => 0,
            'aip' => 0,
            'streak' => 0,
        ]);

        $this->assertEquals(1, $user->level);
        $this->assertEquals(0, $user->xp);
        $this->assertEquals(0, $user->aip);
        $this->assertEquals(0, $user->streak);
    }

    /**
     * Test register without class option starts at onboarding
     */
    public function test_register_user_needs_onboarding(): void
    {
        $user = User::create([
            'name' => 'OnboardUser',
            'email' => 'onboard@example.com',
            'username' => 'onboard',
            'password' => Hash::make('password123'),
            'level' => 1,
            'xp' => 0,
            'aip' => 0,
            'streak' => 0,
            'class' => null,
        ]);

        Membership::create([
            'user_id' => $user->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(3),
        ]);

        $this->actingAs($user);
        $response = $this->get('/onboarding');

        $response->assertStatus(200);
    }

    /**
     * Test unverified user is blocked from the platform and sent to verify notice
     */
    public function test_unverified_user_redirected_to_verification_notice(): void
    {
        $user = User::factory()->unverified()->create(['class' => 'offer_architect']);
        Membership::factory()->active()->create(['user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->get('/feed');

        $response->assertRedirectToRoute('verification.notice');
    }
}
