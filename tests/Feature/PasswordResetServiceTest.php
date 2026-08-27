<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\Auth\PasswordResetService;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class PasswordResetServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_updates_credentials_and_dispatches_its_event_after_commit(): void
    {
        Event::fake([PasswordReset::class]);
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
            'remember_token' => 'old-token',
        ]);

        app(PasswordResetService::class)->reset($user, 'new-password');

        $user = $user->fresh();
        self::assertTrue(Hash::check('new-password', (string) $user->password));
        self::assertNotSame('old-token', $user->remember_token);
        Event::assertDispatched(PasswordReset::class, fn (PasswordReset $event) => $event->user->is($user));
    }
}
