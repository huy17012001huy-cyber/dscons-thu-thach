<?php

declare(strict_types=1);

namespace App\Core\Messaging;

use App\Core\CommunityContext;
use App\Models\Conversation;
use App\Models\DirectMessage;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Support\Facades\DB;

final class ConversationMessageService
{
    public function __construct(private readonly CommunityContext $context) {}

    public function communityConversation(User $user, User $recipient): ?Conversation
    {
        $brand = $this->context->require();
        if ($user->id === $recipient->id || ! $recipient->isCommunityParticipant($brand->id)) {
            return null;
        }
        $ids = [min($user->id, $recipient->id), max($user->id, $recipient->id)];

        return Conversation::query()->firstOrCreate([
            'brand_id' => $brand->id,
            'user_one_id' => $ids[0],
            'user_two_id' => $ids[1],
            'conversation_type' => 'community',
        ]);
    }

    public function open(User $user, int $conversationId): ?Conversation
    {
        $conversation = Conversation::query()->with('contactRequest')->whereKey($conversationId)->first();

        return $conversation && $this->canUse($conversation, $user) ? $conversation : null;
    }

    public function send(User $sender, int $conversationId, string $content, string $notification, string $url): bool
    {
        return DB::transaction(function () use ($sender, $conversationId, $content, $notification, $url): bool {
            $conversation = Conversation::query()->with(['contactRequest', 'userOne', 'userTwo'])->whereKey($conversationId)->lockForUpdate()->first();
            if (! $conversation || ! $this->canUse($conversation, $sender)) {
                return false;
            }
            $message = trim($content);
            if ($message === '') {
                return false;
            }
            DirectMessage::create(['brand_id' => $conversation->brand_id, 'conversation_id' => $conversation->id, 'sender_id' => $sender->id, 'content' => $message]);
            $conversation->update(['last_message_at' => now()]);
            $recipient = $conversation->getOtherUser($sender->id);
            DB::afterCommit(fn () => $recipient->notify(new GenericNotification('message', $notification, $url)));

            return true;
        });
    }

    public function markRead(User $user, int $conversationId): void
    {
        if (! $this->open($user, $conversationId)) {
            return;
        }
        DirectMessage::query()->where('conversation_id', $conversationId)->where('sender_id', '!=', $user->id)->whereNull('read_at')->update(['read_at' => now()]);
    }

    private function canUse(Conversation $conversation, User $user): bool
    {
        $brand = $this->context->require();

        return $conversation->brand_id === $brand->id
            && in_array($user->id, [$conversation->user_one_id, $conversation->user_two_id], true)
            && ($conversation->conversation_type !== 'recruitment' || $conversation->contactRequest?->status === 'accepted');
    }
}
