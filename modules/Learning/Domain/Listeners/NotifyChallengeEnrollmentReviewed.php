<?php

declare(strict_types=1);

namespace Modules\Learning\Domain\Listeners;

use App\Core\CommunityContext;
use App\Models\Expedition;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Modules\Learning\Domain\Events\ChallengeEnrollmentReviewed;

final class NotifyChallengeEnrollmentReviewed implements ShouldQueueAfterCommit
{
    public function __construct(private readonly CommunityContext $context) {}

    public function handle(ChallengeEnrollmentReviewed $event): void
    {
        $challenge = Expedition::withoutGlobalScopes()->with('brand')->find($event->challengeId);
        $brand = $challenge?->brand;
        $learner = User::query()->find($event->learnerId);
        if (! $challenge || ! $brand || ! $learner) {
            return;
        }
        $this->context->run($brand, function () use ($challenge, $learner, $event, $brand): void {
            $approved = $event->status === 'approved';
            $learner->notify(new GenericNotification($approved ? 'check' : 'x', $approved ? 'Bạn đã được duyệt tham gia '.$challenge->title.'! Bấm "Bắt đầu" khi bạn sẵn sàng.' : 'Yêu cầu tham gia '.$challenge->title.' đã bị từ chối.', route('community.challenge.show', ['community' => $brand->slug, 'slug' => $challenge->slug])));
        });
    }
}
