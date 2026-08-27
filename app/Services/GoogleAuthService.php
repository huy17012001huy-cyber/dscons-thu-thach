<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\CommunityContext;
use App\Exceptions\GoogleAuthException;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as ProviderUser;

class GoogleAuthService
{
    public function __construct(private readonly CommunityContext $context) {}

    public function resolveUser(ProviderUser $providerUser): User
    {
        $googleId = trim((string) $providerUser->getId());
        $email = strtolower(trim((string) $providerUser->getEmail()));

        if ($googleId === '' || $email === '') {
            throw new GoogleAuthException('Tài khoản Google không cung cấp đủ thông tin để đăng nhập.');
        }

        if (! $this->providerEmailIsVerified($providerUser)) {
            throw new GoogleAuthException('Email Google chưa được xác minh nên không thể đăng nhập.');
        }

        $existingGoogleUser = User::where('google_id', $googleId)->first();
        if ($existingGoogleUser) {
            return $existingGoogleUser;
        }

        $existingEmailUser = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if ($existingEmailUser) {
            if (! $existingEmailUser->hasVerifiedEmail()) {
                throw new GoogleAuthException('Tài khoản hiện tại chưa xác minh email nên chưa thể liên kết Google.');
            }

            $existingEmailUser->forceFill(['google_id' => $googleId])->save();

            $existingEmailUser->refresh();

            return $existingEmailUser;
        }

        $brand = $this->context->current();
        if (! $brand || $brand->registration_mode !== 'open') {
            throw new GoogleAuthException('Cộng đồng hiện không mở đăng ký tài khoản mới.');
        }

        return DB::transaction(function () use ($providerUser, $googleId, $email, $brand): User {
            $user = User::create([
                'name' => trim((string) ($providerUser->getName() ?: $email)),
                'email' => $email,
                'google_id' => $googleId,
                'username' => $this->uniqueUsername((string) ($providerUser->getName() ?: $email)),
                'avatar' => $providerUser->getAvatar(),
                'source' => 'google',
                'level' => 1,
                'xp' => 0,
                'aip' => 0,
                'streak' => 0,
            ]);
            $user->markEmailAsVerified();

            Membership::create([
                'user_id' => $user->id,
                'status' => 'active',
                'plan' => 'lifetime',
                'expires_at' => '2099-12-31',
            ]);
            $user->brandRoles()->syncWithoutDetaching([
                $brand->id => ['role' => 'member'],
            ]);

            return $user;
        });
    }

    private function providerEmailIsVerified(ProviderUser $providerUser): bool
    {
        $raw = method_exists($providerUser, 'getRaw') ? $providerUser->getRaw() : [];

        return filter_var(data_get($raw, 'verified_email'), FILTER_VALIDATE_BOOL) === true;
    }

    private function uniqueUsername(string $name): string
    {
        $ascii = transliterator_transliterate(
            'Any-Latin; Latin-ASCII; Lower()',
            trim($name)
        ) ?: '';
        $normalized = preg_replace('/\s+/', '.', $ascii) ?? '';
        $username = preg_replace('/[^a-z0-9._]/', '', $normalized) ?: 'user';
        $base = $username;
        $suffix = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base.$suffix++;
        }

        return $username;
    }
}
