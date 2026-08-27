<?php

declare(strict_types=1);

namespace Modules\Learning\Domain\Listeners;

use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Database\Eloquent\Builder;
use Modules\Learning\Domain\Events\ChallengeEnrollmentRequested;

final class NotifyChallengeEnrollmentRequested implements ShouldQueueAfterCommit
{
    public function handle(ChallengeEnrollmentRequested $event): void
    {
        $challenge = $event->challenge;
        $brand = $challenge->brand;
        if ($brand === null) {
            return;
        }
        $url = route('community.challenge.show', [
            'community' => $brand->slug,
            'slug' => $challenge->slug,
        ]);

        User::query()
            ->where(function (Builder $query) use ($challenge): void {
                $query->where('is_admin', true)->orWhereHas('brandRoles', function (Builder $roles) use ($challenge): void {
                    $roles->where('brand_id', $challenge->brand_id)
                        ->whereIn('role', ['owner', 'admin']);
                });
            })
            ->each(fn (User $admin) => $admin->notify(new GenericNotification(
                'check',
                $event->learner->name.' đăng ký tham gia '.$challenge->title,
                $url,
            )));
    }
}
