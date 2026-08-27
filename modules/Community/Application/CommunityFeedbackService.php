<?php

declare(strict_types=1);

namespace Modules\Community\Application;

use App\Core\CommunityContext;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class CommunityFeedbackService
{
    private const TYPES = ['gop_y', 'khieu_nai', 'bao_loi', 'thanh_toan', 'khac'];

    private const STATUSES = ['reviewed', 'resolved'];

    public function __construct(private readonly CommunityContext $context) {}

    /** @param list<string> $attachmentPaths */
    public function submit(User $user, string $type, string $subject, string $content, array $attachmentPaths = []): Feedback
    {
        $brand = $this->context->require();
        if (! $user->isCommunityParticipant($brand->id)) {
            throw new AuthorizationException('Community participation is required.');
        }
        if (! in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException('Unsupported feedback type.');
        }

        return DB::transaction(fn (): Feedback => Feedback::create([
            'brand_id' => $brand->id,
            'user_id' => $user->id,
            'type' => $type,
            'subject' => trim($subject),
            'content' => trim($content),
            'attachments' => $attachmentPaths === [] ? null : $attachmentPaths,
        ]));
    }

    public function updateStatus(int $feedbackId, User $actor, string $status): bool
    {
        if (! in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException('Unsupported feedback status.');
        }

        return DB::transaction(function () use ($feedbackId, $actor, $status): bool {
            $feedback = $this->lockedFeedback($feedbackId, $actor);
            $feedback->update(['status' => $status]);

            return true;
        });
    }

    public function saveNotes(int $feedbackId, User $actor, string $notes): bool
    {
        return DB::transaction(function () use ($feedbackId, $actor, $notes): bool {
            $feedback = $this->lockedFeedback($feedbackId, $actor);
            $feedback->update(['admin_notes' => trim($notes) ?: null]);

            return true;
        });
    }

    public function delete(int $feedbackId, User $actor): bool
    {
        return DB::transaction(function () use ($feedbackId, $actor): bool {
            $this->lockedFeedback($feedbackId, $actor)->delete();

            return true;
        });
    }

    private function lockedFeedback(int $feedbackId, User $actor): Feedback
    {
        $brand = $this->context->require();
        if (! $actor->isCommunityAdmin($brand->id)) {
            throw new AuthorizationException('Community admin access is required.');
        }

        return Feedback::query()
            ->where('brand_id', $brand->id)
            ->whereKey($feedbackId)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
