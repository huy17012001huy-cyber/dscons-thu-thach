<div>
@if($show && $post)
{{-- Panel container — no backdrop, no body lock --}}
<div style="position:fixed; top:0; right:0; bottom:0; width:100%; max-width:560px; z-index:200; pointer-events:none;"
     x-data
     x-on:keydown.escape.window="$wire.close()"
     wire:key="post-modal-{{ $post->id }}">

    {{-- Right Panel --}}
    <div style="pointer-events:auto; background:#FFF; width:100%; height:100%; overflow-y:auto; box-shadow:-2px 0 12px rgba(0,0,0,0.08); border-left:1px solid rgba(0,0,0,0.08);"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full">

        {{-- Header --}}
        <div style="position:sticky; top:0; background:#FFF; border-bottom:1px solid rgba(0,0,0,0.08); padding:10px 16px; display:flex; align-items:center; justify-content:space-between; z-index:2;">
            <span style="font-size:0.8125rem; font-weight:600; color:#6b7280;">Bài viết</span>
            <button wire:click="close" style="width:30px; height:30px; border-radius:6px; display:flex; align-items:center; justify-content:center; cursor:pointer; color:#6b7280; border:none; background:transparent;" onmouseenter="this.style.background='#f0ede8'" onmouseleave="this.style.background='transparent'">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div style="padding:20px;">
            {{-- Author --}}
            <div style="display:flex; align-items:flex-start; gap:12px; margin-bottom:16px;">
                <a href="{{ route('profile', $post->user->username ?? $post->user->id) }}">
                    <img src="{{ $post->user->avatar_url }}" style="width:44px; height:44px; border-radius:50%; object-fit:cover;" alt="">
                </a>
                <div>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <a href="{{ route('profile', $post->user->username ?? $post->user->id) }}" style="font-weight:600; color:#1A1A1A; font-size:0.9rem; text-decoration:none;">{{ $post->user->name }}</a>
                        <span class="badge badge-class-{{ $post->user->class_color }}" style="font-size:0.6rem;">{{ $post->user->class_emoji }} {{ $post->user->class_label }}</span>
                        <span class="level-badge">Lv.{{ $post->user->level }}</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                        @if($post->pillar)
                        <span class="badge badge-pillar-{{ $post->pillar }}" style="font-size:0.6rem;">{{ $post->pillar_label }}</span>
                        @endif
                        @if($post->topic)
                        <span style="font-size:0.75rem; color:#6b7280;">{{ $post->topic->emoji }} {{ $post->topic->name }}</span>
                        @endif
                        <span style="font-size:0.75rem; color:#9ca3af;">{{ $post->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            {{-- Title --}}
            @if($post->title)
            <h2 style="font-size:1.25rem; font-weight:700; color:#1A1A1A; margin-bottom:8px; line-height:1.35;">{{ $post->title }}</h2>
            @endif

            {{-- Images --}}
            @if($post->images->count() > 0)
            <div style="margin-bottom:12px; border-radius:8px; overflow:hidden;">
                @foreach($post->images as $img)
                <a href="{{ asset('storage/' . $img->path) }}" target="_blank" style="display:block; margin-bottom:{{ $loop->last ? '0' : '4px' }};">
                    <img src="{{ asset('storage/' . $img->path) }}" alt="" style="width:100%; max-height:400px; object-fit:cover; border-radius:8px;">
                </a>
                @endforeach
            </div>
            @endif

            {{-- Content --}}
            <div class="post-content prose-post" style="color:#2E2E2E; font-size:0.9rem; line-height:1.75; margin-bottom:16px; overflow-wrap:break-word;">
                {!! $this->renderedPostContent() !!}
            </div>
            <div style="display:none;">
                @php
                    $escaped = e($post->content);
                    $escaped = preg_replace_callback(
                        '#(https?://(?:www\.)?(?:youtube\.com/watch\?v=|youtu\.be/)([a-zA-Z0-9_-]{11})[^\s<]*)#i',
                        fn ($m) => '<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:8px;margin:8px 0;"><iframe src="https://www.youtube.com/embed/'.$m[2].'" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;" allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture" allowfullscreen></iframe></div>',
                        $escaped
                    );
                    $escaped = preg_replace(
                        '#(?<!src="|href=")(https?://(?:(?!&quot;|&lt;|&gt;)[^\s<"])+)#i',
                        '<a href="$1" target="_blank" rel="noopener" style="color:#d17856; text-decoration:underline; word-break:break-all;">$1</a>',
                        $escaped
                    );
                @endphp
                {!! $escaped !!}
            </div>

            {{-- Stats --}}
            <div style="display:flex; align-items:center; gap:16px; padding:10px 0; border-top:1px solid rgba(0,0,0,0.08); border-bottom:1px solid rgba(0,0,0,0.08); margin-bottom:16px;">
                <span style="font-size:0.8125rem; color:#6b7280; display:flex; align-items:center; gap:4px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    {{ $post->likes_count }}
                </span>
                <span style="font-size:0.8125rem; color:#6b7280; display:flex; align-items:center; gap:4px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    {{ $post->all_comments_count }}
                </span>
            </div>

            {{-- Comments --}}
            <div>
                @foreach($post->allComments->whereNull('parent_id') as $comment)
                <div style="display:flex; gap:10px; margin-bottom:14px;">
                    <img src="{{ $comment->user->avatar_url }}" style="width:32px; height:32px; border-radius:50%; object-fit:cover; flex-shrink:0;" alt="">
                    <div style="flex:1; min-width:0;">
                        <div style="background:#f5f4f2; border-radius:8px; padding:8px 12px;">
                            <div style="display:flex; flex-wrap:wrap; align-items:center; gap:6px; margin-bottom:3px;">
                                <span style="font-weight:600; font-size:0.8125rem; color:#1A1A1A;">{{ $comment->user->name }}</span>
                                <span class="badge badge-class-{{ $comment->user->class_color }}" style="font-size:0.6rem;">{{ $comment->user->class_emoji }}</span>
                                <span style="font-size:0.6875rem; color:#9ca3af;">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p style="color:#2E2E2E; font-size:0.8125rem; line-height:1.5; overflow-wrap:break-word;">{!! preg_replace('/@([a-zA-Z0-9._-]+)/', '<a href="/@$1" style="color:#d17856;font-weight:600;text-decoration:none;">@$1</a>', e($comment->content)) !!}</p>
                        </div>
                        @auth
                        <button wire:click="replyTo({{ $comment->id }}, '{{ addslashes($comment->user->name) }}')" style="font-size:0.7rem; color:#6b7280; margin-top:3px; margin-left:8px; cursor:pointer; background:transparent; border:none; padding:0;">Trả lời</button>
                        @endauth

                        @foreach($comment->replies as $reply)
                        <div style="display:flex; gap:8px; margin-top:8px; margin-left:16px;">
                            <img src="{{ $reply->user->avatar_url }}" style="width:26px; height:26px; border-radius:50%; object-fit:cover; flex-shrink:0;" alt="">
                            <div style="flex:1; background:#f0ede8; border-radius:8px; padding:6px 10px;">
                                <div style="display:flex; flex-wrap:wrap; align-items:center; gap:4px; margin-bottom:2px;">
                                    <span style="font-weight:600; font-size:0.75rem; color:#1A1A1A;">{{ $reply->user->name }}</span>
                                    <span class="badge badge-class-{{ $reply->user->class_color }}" style="font-size:0.55rem;">{{ $reply->user->class_emoji }}</span>
                                    <span style="font-size:0.625rem; color:#9ca3af;">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                <p style="color:#2E2E2E; font-size:0.75rem; line-height:1.4; overflow-wrap:break-word;">{!! preg_replace('/@([a-zA-Z0-9._-]+)/', '<a href="/@$1" style="color:#d17856;font-weight:600;text-decoration:none;">@$1</a>', e($reply->content)) !!}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                @if($post->allComments->count() === 0)
                <p style="text-align:center; color:#9ca3af; font-size:0.8125rem; padding:16px 0;">Chưa có bình luận nào</p>
                @endif
            </div>

            {{-- Composer --}}
            @auth
            <div style="position:sticky; bottom:0; background:#fff; padding-top:10px; margin-top:12px; border-top:1px solid rgba(0,0,0,0.08);">
                @if($replyToName)
                <div style="display:flex; align-items:center; gap:6px; font-size:0.72rem; color:#6b7280; margin-bottom:6px;">
                    <span>Trả lời <strong>{{ $replyToName }}</strong></span>
                    <button wire:click="cancelReply" style="color:#dc2626; cursor:pointer; background:transparent; border:none; padding:0;">✕ huỷ</button>
                </div>
                @endif
                <div x-data="postComposerMentions()"
                    x-init="$nextTick(() => textarea = $refs.ta)"
                    style="position:relative;">
                    {{-- Mention dropdown --}}
                    <div x-show="open && $wire.mentionResults.length > 0" x-cloak x-transition
                        @click.outside="close()"
                        style="position:absolute; bottom:100%; left:38px; right:70px; margin-bottom:6px; background:#fff; border:1px solid rgba(0,0,0,0.1); border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.12); max-height:220px; overflow-y:auto; z-index:20;">
                        <template x-for="(u, i) in $wire.mentionResults" :key="u.username">
                            <button type="button" @click="pick(u.username)"
                                @mouseenter="selectedIndex = i"
                                :style="'display:flex; align-items:center; gap:8px; width:100%; text-align:left; padding:6px 10px; cursor:pointer; background:' + (selectedIndex===i ? '#f0ede8' : 'transparent') + '; border:none;'">
                                <img :src="u.avatar_url" style="width:26px; height:26px; border-radius:50%; object-fit:cover; flex-shrink:0;" alt="">
                                <div style="min-width:0; flex:1;">
                                    <div style="font-size:0.78rem; font-weight:600; color:#1A1A1A;" x-text="u.name"></div>
                                    <div style="font-size:0.68rem; color:#6b7280;" x-text="'@' + u.username"></div>
                                </div>
                            </button>
                        </template>
                    </div>

                    <div style="display:flex; gap:8px; align-items:flex-end;">
                        <img src="{{ auth()->user()->avatar_url }}" style="width:30px; height:30px; border-radius:50%; object-fit:cover; flex-shrink:0;" alt="">
                        <textarea wire:model="newComment" x-ref="ta"
                            x-init="$el.style.height = $el.scrollHeight + 'px'"
                            @input="$el.style.height='auto'; $el.style.height=$el.scrollHeight + 'px'; detectMention()"
                            @keydown="onKeydown($event)"
                            rows="1"
                            placeholder="{{ $replyToName ? 'Trả lời '.$replyToName.'...' : 'Viết bình luận... (@ để tag)' }}"
                            style="flex:1; background:#f5f4f2; border:1px solid rgba(0,0,0,0.06); border-radius:18px; padding:8px 12px; font-size:0.85rem; line-height:1.4; resize:none; overflow:hidden; outline:none; max-height:160px;"></textarea>
                        <button wire:click="addComment" wire:loading.attr="disabled" wire:target="addComment"
                            style="padding:6px 14px; font-size:0.8rem; font-weight:600; background:#d17856; color:#fff; border:none; border-radius:18px; cursor:pointer; white-space:nowrap;">
                            <span wire:loading.remove wire:target="addComment">Đăng</span>
                            <span wire:loading wire:target="addComment">...</span>
                        </button>
                    </div>
                </div>
                @error('newComment') <p style="color:#dc2626; font-size:0.7rem; margin-top:4px;">{{ $message }}</p> @enderror
            </div>
            <script>
                function postComposerMentions() {
                    return {
                        open: false,
                        textarea: null,
                        mentionStart: -1,
                        selectedIndex: 0,
                        searchTimer: null,
                        detectMention() {
                            const ta = this.textarea;
                            if (!ta) return;
                            const caret = ta.selectionStart;
                            const before = ta.value.slice(0, caret);
                            const match = before.match(/(?:^|\s)@([a-zA-Z0-9._-]*)$/);
                            if (!match) { this.close(); return; }
                            this.mentionStart = caret - match[1].length - 1;
                            this.selectedIndex = 0;
                            this.open = true;
                            clearTimeout(this.searchTimer);
                            const q = match[1];
                            this.searchTimer = setTimeout(() => this.$wire.searchMentions(q), 120);
                        },
                        close() {
                            this.open = false;
                            this.mentionStart = -1;
                            this.$wire.clearMentions();
                        },
                        onKeydown(e) {
                            if (!this.open || !this.$wire.mentionResults.length) {
                                if (e.key === 'Enter' && !e.shiftKey) {
                                    e.preventDefault();
                                    this.$wire.addComment();
                                }
                                return;
                            }
                            const n = this.$wire.mentionResults.length;
                            if (e.key === 'ArrowDown') { e.preventDefault(); this.selectedIndex = (this.selectedIndex + 1) % n; }
                            else if (e.key === 'ArrowUp') { e.preventDefault(); this.selectedIndex = (this.selectedIndex - 1 + n) % n; }
                            else if (e.key === 'Enter' || e.key === 'Tab') {
                                e.preventDefault();
                                this.pick(this.$wire.mentionResults[this.selectedIndex].username);
                            } else if (e.key === 'Escape') { e.preventDefault(); this.close(); }
                        },
                        pick(username) {
                            const ta = this.textarea;
                            if (!ta || this.mentionStart < 0) return;
                            const caret = ta.selectionStart;
                            const before = ta.value.slice(0, this.mentionStart);
                            const after = ta.value.slice(caret);
                            const insert = '@' + username + ' ';
                            const newVal = before + insert + after;
                            this.$wire.set('newComment', newVal);
                            this.close();
                            this.$nextTick(() => {
                                const pos = before.length + insert.length;
                                ta.focus();
                                ta.setSelectionRange(pos, pos);
                                ta.style.height = 'auto';
                                ta.style.height = ta.scrollHeight + 'px';
                            });
                        },
                    }
                }
            </script>
            @endauth
        </div>
    </div>
</div>
@endif
</div>
