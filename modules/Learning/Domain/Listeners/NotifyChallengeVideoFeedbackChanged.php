<?php

declare(strict_types=1);

namespace Modules\Learning\Domain\Listeners;

use App\Core\CommunityContext;
use App\Models\Expedition;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Database\Eloquent\Builder;
use Modules\Learning\Domain\Events\ChallengeVideoFeedbackChanged;

final class NotifyChallengeVideoFeedbackChanged implements ShouldQueueAfterCommit
{
    public function __construct(private readonly CommunityContext $context) {}

    public function handle(ChallengeVideoFeedbackChanged $event): void
    {
        $challenge = Expedition::withoutGlobalScopes()->with('brand')->find($event->challengeId);
        $brand = $challenge?->brand;
        $learner = User::query()->find($event->learnerId);
        if (! $challenge || ! $brand || ! $learner) {
            return;
        }

        $this->context->run($brand, function () use ($challenge, $event, $learner, $brand): void {
            $url = route('community.challenge.show', ['community' => $brand->slug, 'slug' => $challenge->slug]);
            if ($event->status === 'pending') {
                User::query()->where(function (Builder $users) use ($challenge): void {
                    $users->where('is_admin', true)->orWhereHas('brandRoles', function (Builder $roles) use ($challenge): void {
                        $roles->where('brand_id', $challenge->brand_id)->whereIn('role', ['owner', 'admin']);
                    });
                })->each(fn (User $admin) => $admin->notify(new GenericNotification('play', $learner->name.' gửi Video Feedback cho '.$challenge->title, $url)));

                return;
            }

            $approved = $event->status === 'approved';
            $learner->notify(new GenericNotification(
                $approved ? 'star' : 'x',
                $approved ? 'Video Feedback được duyệt! Ban tổ chức sẽ liên hệ bạn về phần thưởng.' : 'Video Feedback chưa đạt: '.($event->note ?: 'Hãy quay lại video chân thật hơn.'),
                $url,
            ));
        });
    }
}
