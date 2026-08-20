<div class="compose-shell">
    @if(!$expanded)
        <button type="button" wire:click="open" class="compose-trigger card" aria-label="Mở trình tạo bài viết">
            <img src="{{ auth()->user()->avatar_url }}" class="avatar w-10 h-10 shrink-0" alt="">
            <span class="compose-trigger-input">Chia sẻ điều gì đó với cộng đồng...</span>
            <span class="compose-trigger-action">Viết bài</span>
        </button>
    @else
        @teleport('body')
        <div class="compose-modal-backdrop" role="presentation" x-data x-on:keydown.escape.window="$wire.cancel()" @click.self="$wire.cancel()">
            <section class="compose-modal" role="dialog" aria-modal="true" aria-labelledby="compose-title" wire:key="compose-post-modal">
                <header class="compose-modal-header">
                    <h2 id="compose-title">Tạo bài viết</h2>
                    <button type="button" wire:click="cancel" class="compose-close" aria-label="Đóng trình tạo bài viết">×</button>
                </header>

                <div class="compose-modal-body">
                    <div class="compose-author">
                        <img src="{{ auth()->user()->avatar_url }}" class="avatar w-11 h-11 shrink-0" alt="">
                        <div>
                            <strong>{{ auth()->user()->name }}</strong>
                            <span>Đang chia sẻ với cộng đồng DSCons</span>
                        </div>
                    </div>

                    <input wire:model="title" type="text" class="compose-title-input" placeholder="Tiêu đề (không bắt buộc)" aria-label="Tiêu đề bài viết">
                    @error('title') <p class="compose-error" role="alert">{{ $message }}</p> @enderror

                    <div class="compose-editor" x-data="postEditor()" x-init="init()">
                        <div class="compose-toolbar" role="toolbar" aria-label="Định dạng nội dung">
                            <button type="button" @click="replaceSelection('**', '**')" title="In đậm" aria-label="In đậm"><strong>B</strong></button>
                            <button type="button" @click="replaceSelection('*', '*')" title="In nghiêng" aria-label="In nghiêng"><em>I</em></button>
                            <button type="button" @click="replaceSelection('~~', '~~')" title="Gạch ngang" aria-label="Gạch ngang"><s>S</s></button>
                            <span class="compose-toolbar-separator" aria-hidden="true"></span>
                            <button type="button" @click="prefixLines('- ')" title="Danh sách" aria-label="Danh sách">☷</button>
                            <button type="button" @click="prefixLines('1. ')" title="Danh sách đánh số" aria-label="Danh sách đánh số">☰</button>
                            <button type="button" @click="prefixLines('> ')" title="Trích dẫn" aria-label="Trích dẫn">❞</button>
                            <button type="button" @click="insertLink()" title="Chèn liên kết" aria-label="Chèn liên kết">↗</button>
                            <button type="button" @click="insertVideo()" title="Chèn video YouTube" aria-label="Chèn video YouTube">▶</button>
                            <span class="compose-toolbar-spacer"></span>
                            <button type="button" @click="undo()" title="Hoàn tác" aria-label="Hoàn tác">↶</button>
                            <button type="button" @click="redo()" title="Làm lại" aria-label="Làm lại">↷</button>
                            <div class="compose-emoji" x-data="{ emojiOpen: false }">
                                <button type="button" @click="emojiOpen = !emojiOpen" :aria-expanded="emojiOpen.toString()" title="Chèn emoji" aria-label="Chèn emoji">☺</button>
                                <div x-show="emojiOpen" x-cloak @click.outside="emojiOpen = false" class="compose-emoji-menu" role="menu">
                                    @foreach(['😊','🎉','🔥','💡','👏','✅','🚀','❤️','😂','🙏','🤝','📌'] as $emoji)
                                        <button type="button" @click="insertEmoji('{{ $emoji }}'); emojiOpen = false" role="menuitem" aria-label="{{ $emoji }}">{{ $emoji }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <textarea wire:model="content" x-ref="editor" @input="onInput()" placeholder="Chia sẻ điều gì đó với cộng đồng..." aria-label="Nội dung bài viết"></textarea>
                    </div>
                    @error('content') <p class="compose-error" role="alert">{{ $message }}</p> @enderror

                    @if(count($uploadedImages) > 0)
                        <div class="compose-image-previews" aria-label="Ảnh đính kèm">
                            @foreach($uploadedImages as $index => $path)
                                <div class="compose-image-preview">
                                    <img src="{{ asset('storage/'.$path) }}" alt="Ảnh đính kèm {{ $index + 1 }}">
                                    <button type="button" wire:click="removeImage({{ $index }})" aria-label="Xóa ảnh {{ $index + 1 }}">×</button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <footer class="compose-modal-footer">
                    <div class="compose-tools-left">
                        @if(count($uploadedImages) < 4)
                            <label class="compose-tool compose-image-tool" title="Đính kèm ảnh, tối đa 4 ảnh">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m21 15-4.5-4.5L7 20"/></svg>
                                <span>Ảnh</span>
                                <input type="file" wire:model="imageUploads" multiple accept="image/*">
                            </label>
                        @endif
                        <div wire:loading wire:target="imageUploads" class="compose-uploading">Đang tải...</div>

                        <button type="button" wire:click="$toggle('isSignal')" class="compose-tool {{ $isSignal ? 'is-selected' : '' }}" aria-pressed="{{ $isSignal ? 'true' : 'false' }}" title="Tín hiệu ngắn, tối đa 500 từ">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 12a8 8 0 0 1 16 0"/><path d="M7 12a5 5 0 0 1 10 0"/><path d="M10 12a2 2 0 0 1 4 0"/></svg>
                            <span>Tín hiệu</span>
                        </button>
                    </div>

                    <div class="compose-tools-right">
                        <div class="compose-dropdown" x-data="{ open: false }">
                            <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="listbox" class="compose-select {{ $pillar ? 'has-value' : 'is-invalid' }}">
                                {{ $pillar ? $pillars[$pillar]['emoji'].' '.$pillars[$pillar]['label'] : 'Chọn Pillar' }}
                                <span aria-hidden="true">⌄</span>
                            </button>
                            <div x-show="open" x-cloak @click.outside="open = false" class="compose-dropdown-menu" role="listbox">
                                @foreach($pillars as $key => $pillarData)
                                    <button type="button" wire:click="$set('pillar', '{{ $key }}')" @click="open = false" class="compose-pillar-option {{ $pillar === $key ? 'is-selected' : '' }}" role="option" aria-selected="{{ $pillar === $key ? 'true' : 'false' }}">
                                        {{ $pillarData['emoji'] }} {{ $pillarData['label'] }}
                                    </button>
                                @endforeach
                            </div>
                            @error('pillar') <span class="compose-error compose-error-inline" role="alert">{{ $message }}</span> @enderror
                        </div>

                        @if($topics->count())
                            @php($selectedTopic = $topics->firstWhere('id', $topic_id))
                            <div class="compose-dropdown compose-topic" x-data="{ open: false }">
                                <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="listbox" class="compose-select {{ $selectedTopic ? 'has-value' : '' }}">
                                    {{ $selectedTopic ? $selectedTopic->emoji.' '.$selectedTopic->name : 'Chủ đề' }} <span aria-hidden="true">⌄</span>
                                </button>
                                <div x-show="open" x-cloak @click.outside="open = false" class="compose-dropdown-menu" role="listbox">
                                    @if($topic_id)
                                        <button type="button" wire:click="$set('topic_id', null)" @click="open = false" class="compose-pillar-option">Bỏ chọn chủ đề</button>
                                    @endif
                                    @foreach($topics as $topic)
                                        <button type="button" wire:click="$set('topic_id', {{ $topic->id }})" @click="open = false" class="compose-pillar-option {{ $topic_id == $topic->id ? 'is-selected' : '' }}">{{ $topic->emoji }} {{ $topic->name }}</button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <span class="compose-daily-count" title="Giới hạn 5 bài mỗi ngày">{{ $postsToday }}/{{ $dailyPostLimit }} bài hôm nay</span>
                        <button type="button" wire:click="cancel" class="btn btn-ghost compose-cancel">Hủy</button>
                        <button type="button" wire:click="submit" wire:loading.attr="disabled" wire:target="submit" class="btn btn-primary compose-submit" @disabled($remainingPosts === 0)>
                            <span wire:loading.remove wire:target="submit">Đăng</span>
                            <span wire:loading wire:target="submit">Đang đăng...</span>
                        </button>
                    </div>
                </footer>
            </section>
        </div>
        @endteleport
    @endif
</div>
