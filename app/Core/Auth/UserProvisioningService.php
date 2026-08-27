<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Mail\WelcomeMemberMail;
use App\Models\Brand;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class UserProvisioningService
{
    public function provisionWebhookMember(Brand $brand, string $name, string $email, ?User $referrer, string $source): User
    {
        return $this->provision($brand, $name, $email, $referrer, $source, false, false);
    }

    public function provisionAdministratorMember(Brand $brand, string $name, string $email, string $role): User
    {
        return $this->provision($brand, $name, $email, null, 'admin', $role === 'admin', $role === 'mod');
    }

    public function provisionPasswordMember(Brand $brand, string $name, string $email, string $password, ?User $referrer): User
    {
        return $this->provision($brand, $name, $email, $referrer, 'web', false, false, Hash::make($password), false);
    }

    private function provision(Brand $brand, string $name, string $email, ?User $referrer, string $source, bool $isAdmin, bool $isModerator, ?string $passwordHash = null, bool $markVerified = true): User
    {
        return DB::transaction(function () use ($brand, $name, $email, $referrer, $source, $isAdmin, $isModerator, $passwordHash, $markVerified): User {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'username' => $this->uniqueUsername($name),
                'password' => $passwordHash,
                'source' => $source,
                'level' => 1,
                'xp' => 0,
                'aip' => 0,
                'streak' => 0,
                'referred_by' => $referrer?->id,
            ]);
            $user->forceFill(['is_admin' => $isAdmin, 'is_moderator' => $isModerator])->save();
            if ($markVerified) {
                $user->markEmailAsVerified();
            }

            Membership::create([
                'user_id' => $user->id,
                'status' => 'active',
                'plan' => 'lifetime',
                'expires_at' => '2099-12-31',
                'referred_by' => $referrer?->id,
            ]);
            $user->brandRoles()->syncWithoutDetaching([
                $brand->id => ['role' => $isAdmin ? 'admin' : 'member'],
            ]);
            DB::afterCommit(fn () => $this->sendWelcomeEmail($user));

            return $user;
        });
    }

    private function uniqueUsername(string $name): string
    {
        $ascii = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', trim($name)) ?: '';
        $username = preg_replace('/\s+/', '.', $ascii) ?: '';
        $username = preg_replace('/[^a-z0-9._]/', '', $username) ?: 'user';
        $base = $username;
        $suffix = 1;

        while (User::query()->where('username', $username)->exists()) {
            $username = $base.$suffix++;
        }

        return $username;
    }

    private function sendWelcomeEmail(User $user): void
    {
        try {
            Mail::to($user->email)->send(new WelcomeMemberMail(
                name: $user->name,
                email: $user->email,
                loginUrl: route('login'),
                brandName: config('app.name'),
            ));
        } catch (\Throwable $exception) {
            Log::warning('Welcome email could not be sent.', ['user_id' => $user->id]);
        }
    }
}
