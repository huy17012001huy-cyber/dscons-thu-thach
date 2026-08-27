<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class ProfileUpdateService
{
    /** @param array{name:string,username:string,bio:?string,location:?string} $attributes */
    public function update(User $user, array $attributes): User
    {
        return DB::transaction(function () use ($user, $attributes): User {
            $user->update($attributes);

            return $user->fresh() ?? $user;
        });
    }

    public function updateAvatar(User $user, UploadedFile $avatar): string
    {
        $path = $avatar->store('avatars', 'public');
        if (! is_string($path)) {
            throw new \RuntimeException('Avatar storage failed.');
        }

        if ($user->avatar && ! filter_var($user->avatar, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->update(['avatar' => $path]);

        return $path;
    }
}
