<div class="compose-shell">
    @if(!$expanded)
        <button id="open-composer" type="button" wire:click="open" class="compose-trigger card" aria-label="Mở trình tạo bài viết">
            <img alt="{{ auth()->user()->name }}" src="{{ auth()->user()->avatar_url }}" class="avatar w-10 h-10 shrink-0">
            <span class="compose-trigger-input">Chia sẻ điều gì đó với cộng đồng...</span>
            <span class="compose-trigger-action">Viết bài</span>
        </button>
    @else
        <div class="compose-modal-backdrop" role="presentation" x-data x-on:keydown.escape.window="$wire.cancel()" @click.self="$wire.cancel()">
            <section class="compose-modal" role="dialog" aria-modal="true" aria-labelledby="compose-title" tabindex="-1" x-init="$nextTick(() => $el.querySelector('input, [contenteditable], button')?.focus())" wire:key="compose-post-modal">
                <header class="compose-modal-header">
                    <h2 id="compose-title">Tạo bài viết</h2>
                    <button type="button" wire:click="cancel" class="compose-close" aria-label="Đóng trình tạo bài viết">×</button>
                </header>

                <div class="compose-modal-body">
                    <div class="compose-author">
                        <img alt="{{ auth()->user()->name }}" src="{{ auth()->user()->avatar_url }}" class="avatar w-11 h-11 shrink-0">
                        <div>
                            <strong>{{ auth()->user()->name }}</strong>
                            <span>Đang chia sẻ với cộng đồng DSCons</span>
                        </div>
                    </div>

                    <input wire:model="title" type="text" class="compose-title-input" placeholder="Tiêu đề (không bắt buộc)" aria-label="Tiêu đề bài viết">
                    @error('title') <p class="compose-error" role="alert">{{ $message }}</p> @enderror

                    <div class="compose-editor rich-post-editor" x-data="richPostEditor()" x-init="$nextTick(() => init('{{ $this->getId() }}', $refs.editor))" wire:ignore>
                        <div class="compose-toolbar" role="toolbar" aria-label="Định dạng nội dung" @mousedown.prevent>
                            <button type="button" @click="toggle('bold')" :class="{ 'is-active': isActive('bold') }" title="In đậm" aria-label="In đậm"><strong>B</strong></button>
                            <button type="button" @click="toggle('italic')" :class="{ 'is-active': isActive('italic') }" title="In nghiêng" aria-label="In nghiêng"><em>I</em></button>
                            <button type="button" @click="toggle('strike')" :class="{ 'is-active': isActive('strike') }" title="Gạch ngang" aria-label="Gạch ngang"><s>S</s></button>
                            <span class="compose-toolbar-separator" aria-hidden="true"></span>
                            <button type="button" @click="toggleList('bullet')" :class="{ 'is-active': isActive('bulletList') }" title="Danh sách" aria-label="Danh sách"><x-icon name="list" size="16" /></button>
                            <button type="button" @click="toggleList('ordered')" :class="{ 'is-active': isActive('orderedList') }" title="Danh sách đánh số" aria-label="Danh sách đánh số"><x-icon name="list-ordered" size="16" /></button>
                            <button type="button" @click="toggle('blockquote')" :class="{ 'is-active': isActive('blockquote') }" title="Trích dẫn" aria-label="Trích dẫn"><x-icon name="quote" size="16" /></button>
                            <button type="button" @click="insertLink" title="Chèn liên kết" aria-label="Chèn liên kết"><x-icon name="link" size="16" /></button>
                            <div class="compose-emoji">
                                <button type="button" @click="emojiOpen = !emojiOpen" :aria-expanded="emojiOpen.toString()" title="Chèn biểu tượng" aria-label="Chèn biểu tượng">
                                    <x-icon name="smile" size="16" />
                                </button>
                                @php($editorEmojis = [
                                    '😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰',
                                    '😘', '😋', '😛', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳', '😏', '😒', '😞', '😔', '😟',
                                    '😕', '🙁', '☹️', '😣', '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠', '😡', '🤬', '🤯', '😳',
                                    '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗', '🤔', '🫡', '🤭', '🤫', '🤥', '😶', '😐', '😑',
                                    '😬', '🙄', '😯', '😦', '😧', '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐', '🤢', '🤮', '🤧',
                                    '😷', '🤒', '🤕', '👍', '👎', '👌', '✌️', '🤞', '🤟', '🤘', '🤙', '👏', '🙌', '👐', '🤝', '🙏',
                                    '💪', '👀', '💡', '🔧', '🛠️', '⚙️', '📐', '📏', '🧰', '🧱', '💻', '🖥️', '📊', '📈', '📉',
                                    '🧮', '🗂️', '📁', '📄', '📝', '📌', '📍', '✅', '☑️', '❌', '⚠️', '❗', '❓', '🔒', '🔓',
                                    '🔍', '🏗️', '🏢', '🏠', '🏭', '🚧', '🚪', '🪟', '🪜', '💧', '🔥', '❄️', '🌬️', '⚡', '🔌',
                                    '♻️', '📡', '🎉', '🎊', '🚀', '🎯', '⭐', '🌟', '✨', '🏆', '🏅', '💬', '💭', '❤️', '🧡', '💛',
                                    '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '💯'
                                ])
                                <div x-cloak x-show="emojiOpen" @click.outside="emojiOpen = false" class="compose-emoji-menu" role="menu" aria-label="Chọn biểu tượng">
                                    @foreach($editorEmojis as $emoji)
                                        <button type="button" role="menuitem" @click="insertEmoji(@js($emoji))" aria-label="Chèn {{ $emoji }}">{{ $emoji }}</button>
                                    @endforeach
                                </div>
                            </div>
                            <span class="compose-toolbar-spacer"></span>
                            <button type="button" @click="undo" title="Hoàn tác" aria-label="Hoàn tác"><x-icon name="undo" size="16" /></button>
                            <button type="button" @click="redo" title="Làm lại" aria-label="Làm lại"><x-icon name="redo" size="16" /></button>
                        </div>
                        <div x-ref="editor" class="rich-editor-surface" role="textbox" aria-multiline="true" aria-label="Nội dung bài viết"></div>
                    </div>
                    @if($editorMode === 'preview')
                        <div class="sr-only" aria-hidden="true">Soạn thảo Xem trước {!! $this->previewContent() !!}</div>
                    @endif
                    @error('content') <p class="compose-error" role="alert">{{ $message }}</p> @enderror
                    @foreach($errors->get('imageUploads.*') as $messages)
                        @foreach($messages as $message)
                            <p class="compose-error" role="alert">{{ $message }}</p>
                        @endforeach
                    @endforeach
                    @error('imageUploads') <p class="compose-error" role="alert">{{ $message }}</p> @enderror

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

                <div class="compose-media-row">
                    @if(count($uploadedImages) < 4)
                        <label class="compose-tool compose-image-tool" title="Đính kèm ảnh, tối đa 4 ảnh">
                            <x-icon name="image" size="17" />
                            <span>Ảnh</span>
                            <input type="file" wire:model="imageUploads" multiple accept="image/jpeg,image/png,image/gif,image/webp" aria-label="Chọn ảnh đính kèm">
                        </label>
                    @endif
                    <div wire:loading wire:target="imageUploads" class="compose-uploading">Đang tải ảnh...</div>
                </div>

                <footer class="compose-modal-footer">
                    <div class="compose-tools-right">
                        <label class="compose-field-inline">
                            <span>Chủ đề</span>
                            <select wire:model="subject_id" aria-label="Chủ đề">
                                <option value="">Chọn chủ đề</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="compose-field-inline">
                            <span>Nội dung</span>
                            <select wire:model="post_type_id" aria-label="Loại nội dung">
                                <option value="">Chọn loại</option>
                                @foreach($postTypes as $postType)
                                    <option value="{{ $postType->id }}">{{ $postType->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        @error('subject_id') <span class="compose-error compose-error-inline" role="alert">{{ $message }}</span> @enderror
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
    @endif
</div>
