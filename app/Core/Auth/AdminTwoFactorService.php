<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

final class AdminTwoFactorService
{
    public function __construct(private readonly Google2FA $totp) {}

    /** @return array{secret: string, recovery_codes: list<string>, otp_auth_url: string} */
    public function prepare(User $user): array
    {
        $secret = $this->totp->generateSecretKey();
        $recoveryCodes = $this->recoveryCodes();

        return [
            'secret' => $secret,
            'recovery_codes' => $recoveryCodes,
            'otp_auth_url' => $this->totp->getQRCodeUrl(config('app.name'), $user->email, $secret),
        ];
    }

    /** @param list<string> $recoveryCodes */
    public function confirm(User $user, string $secret, string $code, array $recoveryCodes): bool
    {
        if (! $this->totp->verifyKey($secret, $this->normaliseCode($code), 1)) {
            return false;
        }

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => array_map(fn (string $recoveryCode): string => Hash::make($recoveryCode), $recoveryCodes),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return true;
    }

    public function verify(User $user, string $code): bool
    {
        $secret = $user->two_factor_secret;

        return is_string($secret)
            && $user->two_factor_confirmed_at !== null
            && $this->totp->verifyKey($secret, $this->normaliseCode($code), 1);
    }

    public function useRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes;

        if (! is_array($codes)) {
            return false;
        }

        foreach ($codes as $index => $hash) {
            if (! Hash::check($code, $hash)) {
                continue;
            }

            unset($codes[$index]);
            $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

            return true;
        }

        return false;
    }

    public function disable(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }

    private function normaliseCode(string $code): string
    {
        return preg_replace('/\s+/', '', $code) ?? '';
    }

    /** @return list<string> */
    private function recoveryCodes(): array
    {
        $codes = [];

        for ($index = 0; $index < (int) config('admin-security.recovery_code_count'); $index++) {
            $codes[] = Str::upper(Str::random(10));
        }

        return $codes;
    }
}
