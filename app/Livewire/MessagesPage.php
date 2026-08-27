<?php

namespace App\Livewire;

use App\Core\Messaging\ConversationMessageService;
use App\Models\Conversation;
use App\Models\RecruitmentContactRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Rule;
use Livewire\Component;

class MessagesPage extends Component
{
    public ?int $activeConversationId = null;

    public ?int $startWithUserId = null;

    #[Rule('required|min:1|max:2000')]
    public string $newMessage = '';

    public function mount(?int $conversation = null): void
    {
        $user = $this->currentUser();
        if (! $user) {
            return;
        }

        $userId = request()->query('user');
        if ($userId && (int) $userId !== $user->id) {
            $target = User::find((int) $userId);
            if (! $target instanceof User) {
                return;
            }
            if ($target->isRecruiter()) {
                $request = $this->acceptedRecruitmentRequest((int) $userId);
                $conv = $request ? Conversation::where('contact_request_id', $request->id)->first() : null;
                $this->activeConversationId = $conv?->id;
            } else {
                $this->activeConversationId = app(ConversationMessageService::class)->communityConversation($user, $target)?->id;
            }
        } elseif ($conversation) {
            // Verify current user is a participant
            $this->activeConversationId = app(ConversationMessageService::class)->open($user, $conversation)?->id;
        }
    }

    public function openConversation(int $id): void
    {
        $user = $this->currentUser();
        if (! $user || ! app(ConversationMessageService::class)->open($user, $id)) {
            return;
        }
        $this->activeConversationId = $id;
        $this->markAsRead($id);
    }

    public function sendMessage(): void
    {
        $user = $this->currentUser();
        if (! $user || ! $this->activeConversationId) {
            return;
        }
        $this->validate();

        $throttleKey = 'send-message:'.$user->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'newMessage' => "Bạn đang gửi tin nhắn quá nhanh. Vui lòng thử lại sau $seconds giây.",
            ]);
        }

        RateLimiter::hit($throttleKey);

        if (! app(ConversationMessageService::class)->send($user, $this->activeConversationId, $this->newMessage, $user->name.' gửi tin nhắn cho bạn', route('messages', ['conversation' => $this->activeConversationId]))) {
            return;
        }

        $this->newMessage = '';
    }

    private function markAsRead(int $conversationId): void
    {
        $user = $this->currentUser();
        if (! $user) {
            return;
        }

        app(ConversationMessageService::class)->markRead($user, $conversationId);
    }

    public function render(): View
    {
        $user = $this->currentUser();
        abort_unless($user instanceof User, 403);
        $userId = $user->id;

        $conversations = Conversation::where(function ($query) use ($userId) {
            $query->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
        })->where(function ($query) {
            $query->where('conversation_type', 'community')
                ->orWhereHas('contactRequest', fn ($request) => $request->where('status', 'accepted'));
        })
            ->with(['userOne', 'userTwo', 'lastMessage'])
            ->orderByDesc('last_message_at')
            ->get();

        $messages = collect();
        $otherUser = null;
        if ($this->activeConversationId) {
            $conv = $conversations->firstWhere('id', $this->activeConversationId);
            if ($conv) {
                $otherUser = $conv->getOtherUser($userId);
                $messages = $conv->messages()
                    ->with('sender')
                    ->oldest()
                    ->limit(100)
                    ->get();
                $this->markAsRead($this->activeConversationId);
            }
        }

        return view('livewire.messages-page', [
            'conversations' => $conversations,
            'messages' => $messages,
            'otherUser' => $otherUser,
        ])->layout('layouts.app', ['title' => 'Tin nhắn — DSCons']);
    }

    private function acceptedRecruitmentRequest(int $otherUserId): ?RecruitmentContactRequest
    {
        $user = $this->currentUser();
        if (! $user) {
            return null;
        }

        return RecruitmentContactRequest::query()->where('status', 'accepted')->where(function ($query) use ($otherUserId, $user) {
            $query->where('recruiter_id', $user->id)->where('engineer_id', $otherUserId)
                ->orWhere('recruiter_id', $otherUserId)->where('engineer_id', $user->id);
        })->latest()->first();
    }

    private function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }
}
