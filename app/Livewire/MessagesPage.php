<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\DirectMessage;
use App\Notifications\GenericNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
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
        $userId = request()->query('user');
        if ($userId && (int)$userId !== Auth::id()) {
            $conv = Conversation::findOrCreateBetween(Auth::id(), (int)$userId);
            $this->activeConversationId = $conv->id;
        } elseif ($conversation) {
            // Verify current user is a participant
            $conv = Conversation::find($conversation);
            if ($conv && ($conv->user_one_id === Auth::id() || $conv->user_two_id === Auth::id())) {
                $this->activeConversationId = $conversation;
            }
        }
    }

    public function openConversation(int $id): void
    {
        $conv = Conversation::findOrFail($id);
        if ($conv->user_one_id !== Auth::id() && $conv->user_two_id !== Auth::id()) return;
        $this->activeConversationId = $id;
        $this->markAsRead($id);
    }

    public function sendMessage(): void
    {
        if (!Auth::check() || !$this->activeConversationId) return;
        $this->validate();

        $throttleKey = 'send-message:' . Auth::id();
        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'newMessage' => "Bạn đang gửi tin nhắn quá nhanh. Vui lòng thử lại sau $seconds giây."
            ]);
        }

        RateLimiter::hit($throttleKey);

        $conv = Conversation::findOrFail($this->activeConversationId);
        $user = Auth::user();

        // Verify user is participant
        if ($conv->user_one_id !== $user->id && $conv->user_two_id !== $user->id) return;

        DirectMessage::create([
            'conversation_id' => $conv->id,
            'sender_id' => $user->id,
            'content' => $this->newMessage,
        ]);

        $conv->update(['last_message_at' => now()]);

        // Notify other user
        $other = $conv->getOtherUser($user->id);
        $other->notify(new GenericNotification(
            '💬', $user->name . ' gửi tin nhắn cho bạn',
            route('messages', ['conversation' => $conv->id])
        ));

        $this->newMessage = '';
    }

    private function markAsRead(int $conversationId): void
    {
        DirectMessage::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function render()
    {
        $userId = Auth::id();

        $conversations = Conversation::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->with(['userOne', 'userTwo', 'lastMessage'])
            ->orderByDesc('last_message_at')
            ->get();

        $messages = collect();
        $otherUser = null;
        if ($this->activeConversationId) {
            $conv = $conversations->firstWhere('id', $this->activeConversationId);
            if ($conv) {
                $otherUser = $conv->getOtherUser($userId);
                $messages = DirectMessage::where('conversation_id', $this->activeConversationId)
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
}
