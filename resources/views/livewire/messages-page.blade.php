<div class="flex gap-0" style="height:calc(100vh - 120px); min-height:400px;">

    {{-- Conversation list (left) --}}
    <div class="{{ $activeConversationId ? 'hidden md:flex' : 'flex' }} flex-col" style="width:300px; flex-shrink:0; border-right:1px solid #E1E1E1; background:#FFF; border-radius:0.75rem 0 0 0.75rem; overflow:hidden;">
        <div style="padding:0.875rem; border-bottom:1px solid #E1E1E1;">
            <h1 style="font-size:1rem; font-weight:800; color:#1A1A1A;">💬 Tin nhắn</h1>
        </div>
        <div style="flex:1; overflow-y:auto;">
            @forelse($conversations as $conv)
            @php
                $other = $conv->getOtherUser(auth()->id());
                $unread = $conv->unreadCount(auth()->id());
                $isActive = $activeConversationId === $conv->id;
            @endphp
            <button wire:click="openConversation({{ $conv->id }})" style="display:flex; gap:0.625rem; padding:0.75rem; width:100%; text-align:left; cursor:pointer; transition:background 0.1s; {{ $isActive ? 'background:#E8F5E9;' : '' }}" class="hover:bg-gray-50">
                <img src="{{ $other->avatar_url }}" class="avatar w-10 h-10 shrink-0" alt="">
                <div style="flex:1; min-width:0;">
                    <div class="flex items-center justify-between">
                        <p style="font-size:0.8rem; font-weight:{{ $unread ? '700' : '500' }}; color:#1A1A1A; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $other->name }}</p>
                        @if($conv->lastMessage)
                        <span style="font-size:0.6rem; color:#5C5C66; white-space:nowrap;">{{ $conv->lastMessage->created_at->shortRelativeDiffForHumans() }}</span>
                        @endif
                    </div>
                    @if($conv->lastMessage)
                    <p style="font-size:0.725rem; color:{{ $unread ? '#1A1A1A' : '#5C5C66' }}; font-weight:{{ $unread ? '600' : '400' }}; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        {{ $conv->lastMessage->sender_id === auth()->id() ? 'Bạn: ' : '' }}{{ Str::limit($conv->lastMessage->content, 40) }}
                    </p>
                    @endif
                </div>
                @if($unread)
                <span style="background:#d17856; color:#FFF; font-size:0.6rem; font-weight:700; min-width:18px; height:18px; border-radius:999px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">{{ $unread }}</span>
                @endif
            </button>
            @empty
            <div style="padding:2rem 1rem; text-align:center;">
                <p style="font-size:1.5rem; margin-bottom:0.5rem;">💬</p>
                <p style="font-size:0.8rem; color:#5C5C66;">Chưa có tin nhắn nào</p>
                <p style="font-size:0.75rem; color:#5C5C66; margin-top:0.25rem;">Bấm vào profile của thành viên để gửi tin nhắn</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Chat area (right) --}}
    <div class="{{ $activeConversationId ? 'flex' : 'hidden md:flex' }} flex-col flex-1" style="background:#FFF; border-radius:0 0.75rem 0.75rem 0; overflow:hidden;">
        @if($otherUser)
        {{-- Chat header --}}
        <div class="flex items-center gap-3" style="padding:0.75rem 1rem; border-bottom:1px solid #E1E1E1;">
            <button wire:click="$set('activeConversationId', null)" class="md:hidden" style="color:#5C5C66; cursor:pointer;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <a href="{{ route('profile', $otherUser->username ?? $otherUser->id) }}" class="flex items-center gap-2">
                <img src="{{ $otherUser->avatar_url }}" class="avatar w-8 h-8" alt="">
                <div>
                    <p style="font-size:0.85rem; font-weight:600; color:#1A1A1A;">{{ $otherUser->name }}</p>
                    <p style="font-size:0.65rem; color:#5C5C66;">{{ $otherUser->class_label }} · Lv.{{ $otherUser->level }}</p>
                </div>
            </a>
        </div>

        {{-- Messages --}}
        <div style="flex:1; overflow-y:auto; padding:1rem;" x-data x-init="$el.scrollTop = $el.scrollHeight" wire:poll.5s>
            @foreach($messages as $msg)
            @php $isMe = $msg->sender_id === auth()->id(); @endphp
            <div class="flex mb-2 {{ $isMe ? 'justify-end' : 'justify-start' }}">
                <div style="max-width:75%; padding:0.5rem 0.75rem; border-radius:1rem; font-size:0.825rem; line-height:1.5; word-break:break-word;
                    {{ $isMe
                        ? 'background:#d17856; color:#FFF; border-bottom-right-radius:0.25rem;'
                        : 'background:#F7F5F3; color:#1A1A1A; border-bottom-left-radius:0.25rem;' }}">
                    {{ $msg->content }}
                    <p style="font-size:0.55rem; margin-top:0.25rem; {{ $isMe ? 'color:rgba(255,255,255,0.6);' : 'color:#5C5C66;' }} text-align:right;">{{ $msg->created_at->setTimezone('Asia/Ho_Chi_Minh')->format('H:i') }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Message input --}}
        <div style="padding:0.75rem 1rem; border-top:1px solid #E1E1E1;">
            <form wire:submit="sendMessage" class="flex gap-2">
                <input wire:model="newMessage" type="text" class="input" placeholder="Nhập tin nhắn..." style="flex:1; font-size:0.85rem;" autocomplete="off">
                <button type="submit" wire:loading.attr="disabled" wire:loading.class="opacity-50" wire:target="sendMessage" class="btn btn-primary" style="padding:0.375rem 0.875rem; font-size:0.8rem; white-space:nowrap;">Gửi</button>
            </form>
            @error('newMessage') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
        </div>
        @else
        {{-- No conversation selected --}}
        <div style="flex:1; display:flex; align-items:center; justify-content:center;">
            <div style="text-align:center;">
                <p style="font-size:2.5rem; margin-bottom:0.5rem;">💬</p>
                <p style="font-size:0.9rem; color:#5C5C66;">Chọn cuộc trò chuyện để bắt đầu</p>
            </div>
        </div>
        @endif
    </div>
</div>
