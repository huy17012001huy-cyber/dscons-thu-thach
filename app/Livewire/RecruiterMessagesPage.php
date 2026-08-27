<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\DirectMessage;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Component;

class RecruiterMessagesPage extends Component
{
    public ?int $activeConversationId = null;

    public string $newMessage = '';

    public function mount(?int $conversation = null): void
    {
        if ($conversation) {
            $this->openConversation($conversation);
        }
    }

    public function openConversation(int $id): void
    {
        $conversation = $this->conversations()->firstWhere('id', $id);
        if ($conversation) {
            $this->activeConversationId = $conversation->id;
            $this->markAsRead($conversation->id);
        }
    }

    public function sendMessage(): void
    {
        $this->validate(['newMessage' => 'required|min:1|max:2000']);
        abort_unless($this->activeConversationId !== null, 422);
        $user = $this->currentUser();
        $rateLimitKey = 'recruiter-message:'.$user->id;
        abort_if(RateLimiter::tooManyAttempts($rateLimitKey, 10), 429);
        RateLimiter::hit($rateLimitKey);

        $conversation = $this->conversations()->first(fn ($item) => $item->id === $this->activeConversationId);
        abort_unless($conversation instanceof Conversation, 404);
        DirectMessage::create(['conversation_id' => $conversation->id, 'sender_id' => $user->id, 'content' => $this->newMessage, 'brand_id' => brand()->id]);
        $conversation->update(['last_message_at' => now()]);
        $conversation->getOtherUser($user->id)->notify(new GenericNotification('💬', 'Nhà tuyển dụng gửi tin nhắn cho bạn.', community_route('messages', ['conversation' => $conversation->id])));
        $this->newMessage = '';
    }

    /** @return Collection<int, Conversation> */
    private function conversations(): Collection
    {
        $user = $this->currentUser();

        return Conversation::query()
            ->where('brand_id', brand()->id)
            ->where('conversation_type', 'recruitment')
            ->whereHas('contactRequest', fn ($query) => $query->where('brand_id', brand()->id)->where('status', 'accepted'))
            ->where(fn ($query) => $query->where('user_one_id', $user->id)->orWhere('user_two_id', $user->id))
            ->with(['userOne', 'userTwo', 'lastMessage', 'contactRequest'])
            ->latest('last_message_at')
            ->get();
    }

    private function markAsRead(int $conversationId): void
    {
        DirectMessage::where('brand_id', brand()->id)->where('conversation_id', $conversationId)->where('sender_id', '!=', $this->currentUser()->id)->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function render(): View
    {
        $conversations = $this->conversations();
        $active = $conversations->firstWhere('id', $this->activeConversationId);
        $messages = $active ? DirectMessage::where('brand_id', brand()->id)->where('conversation_id', $active->id)->with('sender')->oldest()->limit(100)->get() : collect();

        return view('livewire.recruiter-messages-page', compact('conversations', 'active', 'messages'))->layout('layouts.recruiter', ['title' => 'Tin nhắn tuyển dụng']);
    }

    private function currentUser(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
