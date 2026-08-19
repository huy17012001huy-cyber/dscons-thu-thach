<div>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 style="font-size:1.25rem; font-weight:800; color:#0F3D5E;">Hỏi đáp kỹ thuật</h1>
            <p style="font-size:0.8rem; color:#61798A; margin-top:0.15rem;">Cùng tháo gỡ bài toán MEP, BIM, AI và Vibe Coding.</p>
        </div>
        @auth
        <button wire:click="$toggle('showAsk')" aria-pressed="{{ $showAsk ? 'true' : 'false' }}" class="btn btn-primary" style="font-size:0.875rem;">
            + Đặt câu hỏi
        </button>
        @endauth
    </div>

    @if($showAsk)
    <div class="card mb-4">
        <h3 style="font-size:0.9rem; font-weight:700; color:#1A1A1A; margin-bottom:1rem;">Câu hỏi của bạn</h3>
        <div class="flex flex-col gap-3">
            <div>
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#2E2E2E; margin-bottom:0.375rem;">Tiêu đề *</label>
                <input wire:model="title" class="input" placeholder="Câu hỏi ngắn gọn của bạn...">
                @error('title') <p style="color:#991B1B; font-size:0.75rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
            </div>
            <div>
                <label style="display:block; font-size:0.8rem; font-weight:600; color:#2E2E2E; margin-bottom:0.375rem;">Chi tiết (tùy chọn)</label>
                <textarea wire:model="body" class="input" rows="3" placeholder="Mô tả thêm context..."
                    x-data x-init="$el.style.height = $el.scrollHeight + 'px'"
                    @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                    style="overflow:hidden; resize:none;"></textarea>
            </div>
            <div class="flex flex-wrap gap-3 items-center">
                <div class="flex gap-1">
                    @foreach(['offer'=>'🔥','traffic'=>'✨','conversion'=>'🎯','delivery'=>'⚙️','continuity'=>'🔗'] as $p => $e)
                    <button wire:click="$set('pillar','{{ $p }}')" class="badge badge-pillar-{{ $p }}" style="cursor:pointer; padding:0.25rem 0.5rem; {{ $pillar === $p ? '' : 'opacity:0.4;' }}">{{ $e }}</button>
                    @endforeach
                </div>
                <label class="flex items-center gap-1.5" style="font-size:0.8rem; color:#2E2E2E; cursor:pointer;">
                    <input wire:model="isAnonymous" type="checkbox" style="accent-color:#d17856;">
                    Ẩn danh
                </label>
                <div class="flex gap-2 ml-auto">
                    <button wire:click="$set('showAsk',false)" class="btn btn-ghost" style="font-size:0.8rem;">Hủy</button>
                    <button wire:click="submitQuestion" wire:loading.attr="disabled" wire:loading.class="opacity-50" wire:target="submitQuestion" class="btn btn-primary" style="font-size:0.875rem;">Đăng câu hỏi</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="tab-nav">
        <button wire:click="setFilter('all')" class="tab-item {{ $filter === 'all' ? 'active' : '' }}">Tất cả</button>
        <button wire:click="setFilter('unanswered')" class="tab-item {{ $filter === 'unanswered' ? 'active' : '' }}">Chưa trả lời</button>
        <button wire:click="setFilter('answered')" class="tab-item {{ $filter === 'answered' ? 'active' : '' }}">Đã trả lời</button>
        @auth <button wire:click="setFilter('mine')" class="tab-item {{ $filter === 'mine' ? 'active' : '' }}">Của tôi</button> @endauth
    </div>

    <div class="flex flex-col gap-3">
        @forelse($questions as $q)
        <div class="card" style="padding:0; overflow:hidden;">
            {{-- Question header --}}
            <button wire:click="toggleQuestion({{ $q->id }})" class="w-full text-left" style="padding:1rem; cursor:pointer;">
                <div class="flex items-start gap-3">
                    @if($q->is_anonymous)
                    <div style="width:36px; height:36px; border-radius:50%; background:#EEECE9; display:flex; align-items:center; justify-content:center; font-size:0.875rem; flex-shrink:0;">❓</div>
                    @else
                    <img src="{{ $q->user->avatar_url }}" class="avatar w-9 h-9 shrink-0" alt="">
                    @endif
                    <div style="flex:1; min-width:0;">
                        <h3 style="font-size:0.9rem; font-weight:600; color:#1A1A1A; line-height:1.4;">{{ $q->title }}</h3>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <span style="font-size:0.75rem; color:#5C5C66;">{{ $q->is_anonymous ? 'Ẩn danh' : $q->user->name }}</span>
                            @if($q->pillar)
                            <span class="badge badge-pillar-{{ $q->pillar }}" style="font-size:0.65rem;">{{ match($q->pillar){'offer'=>'Offer','traffic'=>'Thu hút','conversion'=>'Chuyển đổi','delivery'=>'Cung ứng','continuity'=>'Continuity'} }}</span>
                            @endif
                            <span style="font-size:0.7rem; color:#5C5C66;">{{ $q->created_at->diffForHumans() }}</span>
                        </div>
                        @if($q->body && $openQuestionId !== $q->id)
                        <p style="font-size:0.8rem; color:#5C5C66; margin-top:0.5rem; line-height:1.5;">{{ Str::limit($q->body, 120) }}</p>
                        @endif
                    </div>
                    <div class="shrink-0 text-right">
                        @if($q->status === 'answered')
                        <span class="badge" style="background:#D1FAE5; color:#065F46; font-size:0.65rem;">✓ Đã trả lời</span>
                        @else
                        <span class="badge" style="background:#E8F5E9; color:#1B5E20; font-size:0.65rem;">Chờ trả lời</span>
                        @endif
                        <p style="font-size:0.7rem; color:#5C5C66; margin-top:0.25rem;">{{ $q->answers_count }} trả lời</p>
                    </div>
                </div>
            </button>

            {{-- Expanded: full body + answers + reply form --}}
            @if($openQuestionId === $q->id)
            <div style="border-top:1px solid #E1E1E1; padding:1rem;">
                @auth
                @if(auth()->user()->is_admin && $editingQuestionId !== $q->id)
                <div class="flex justify-end" style="margin-bottom:0.5rem;">
                    <div x-data="{ open: false }" style="position:relative;">
                        <button @click.stop="open = !open" style="background:none; border:none; cursor:pointer; padding:2px 6px; font-size:1rem; color:#9ca3af; line-height:1;">⋯</button>
                        <div x-show="open" @click.away="open = false" x-transition x-cloak
                             style="position:absolute; right:0; top:calc(100% + 4px); z-index:50; background:#fff; border:1px solid #e5e7eb; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); min-width:120px; overflow:hidden;">
                            <button @click.stop="open = false; $wire.editQuestion({{ $q->id }})" style="display:block; width:100%; text-align:left; padding:8px 14px; font-size:0.8rem; color:#2E2E2E; background:none; border:none; cursor:pointer;" onmouseover="this.style.background='#f5f4f2'" onmouseout="this.style.background='none'">Sửa câu hỏi</button>
                            <button @click.stop="open = false" wire:click="deleteQuestion({{ $q->id }})" wire:confirm="Xóa câu hỏi này? Mọi câu trả lời cũng sẽ bị xóa." style="display:block; width:100%; text-align:left; padding:8px 14px; font-size:0.8rem; color:#DC2626; background:none; border:none; cursor:pointer;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='none'">Xóa câu hỏi</button>
                        </div>
                    </div>
                </div>
                @endif
                @endauth

                @if($editingQuestionId === $q->id)
                <div style="margin-bottom:1rem;">
                    <label style="display:block; font-size:0.8rem; font-weight:600; color:#2E2E2E; margin-bottom:0.375rem;">Tiêu đề *</label>
                    <input wire:model="editingQuestionTitle" class="input" style="font-size:0.85rem;">
                    @error('editingQuestionTitle') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
                    <label style="display:block; font-size:0.8rem; font-weight:600; color:#2E2E2E; margin:0.75rem 0 0.375rem;">Nội dung</label>
                    <textarea wire:model="editingQuestionBody" class="input" rows="3" style="font-size:0.85rem; overflow:hidden; resize:none;"
                        x-data x-init="$el.style.height = $el.scrollHeight + 'px'"
                        @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"></textarea>
                    @error('editingQuestionBody') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
                    <div class="flex gap-2 justify-end mt-2">
                        <button wire:click="cancelEditQuestion" class="btn btn-ghost" style="font-size:0.8rem; padding:0.25rem 0.6rem;">Hủy</button>
                        <button wire:click="updateQuestion" class="btn btn-primary" style="font-size:0.8rem; padding:0.25rem 0.6rem;">Lưu</button>
                    </div>
                </div>
                @elseif($q->body)
                <p style="font-size:0.85rem; color:#2E2E2E; line-height:1.6; margin-bottom:1rem; white-space:pre-line;">{{ $q->body }}</p>
                @endif

                {{-- Existing answers --}}
                @foreach($q->answers as $a)
                <div class="flex gap-2 mb-3">
                    <img src="{{ $a->user->avatar_url }}" class="avatar w-8 h-8 shrink-0" alt="">
                    <div style="flex:1; background:#F7F5F3; border-radius:0.5rem; padding:0.625rem 0.875rem;">
                        <div class="flex items-center gap-2 mb-1">
                            <span style="font-weight:600; font-size:0.8rem; color:#1A1A1A;">{{ $a->user->name }}</span>
                            @if($a->is_best)
                            <span class="badge" style="background:#D1FAE5; color:#065F46; font-size:0.6rem;">✓ Trả lời hay nhất</span>
                            @endif
                            <span style="font-size:0.7rem; color:#5C5C66;">{{ $a->created_at->diffForHumans() }}</span>
                            @auth
                            @if($a->user_id === auth()->id() || auth()->user()->is_admin)
                            <div x-data="{ open: false }" style="position:relative; margin-left:auto;">
                                <button @click.stop="open = !open" style="background:none; border:none; cursor:pointer; padding:2px 4px; font-size:1rem; color:#9ca3af; line-height:1;">⋯</button>
                                <div x-show="open" @click.away="open = false" x-transition x-cloak
                                     style="position:absolute; right:0; top:calc(100% + 4px); z-index:50; background:#fff; border:1px solid #e5e7eb; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); min-width:120px; overflow:hidden;">
                                    <button @click.stop="open = false; $wire.editAnswer({{ $a->id }})" style="display:block; width:100%; text-align:left; padding:8px 14px; font-size:0.8rem; color:#2E2E2E; background:none; border:none; cursor:pointer;" onmouseover="this.style.background='#f5f4f2'" onmouseout="this.style.background='none'">Sửa</button>
                                    <button @click.stop="open = false" wire:click="deleteAnswer({{ $a->id }})" wire:confirm="Xóa câu trả lời này?" style="display:block; width:100%; text-align:left; padding:8px 14px; font-size:0.8rem; color:#DC2626; background:none; border:none; cursor:pointer;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='none'">Xóa</button>
                                </div>
                            </div>
                            @endif
                            @endauth
                        </div>
                        @if($editingAnswerId === $a->id)
                        <div>
                            <textarea wire:model="editingAnswerBody" class="input" rows="2" style="font-size:0.8rem;"
                                x-data x-init="$el.style.height = $el.scrollHeight + 'px'"
                                @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                style="overflow:hidden; resize:none;"></textarea>
                            @error('editingAnswerBody') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
                            <div class="flex gap-2 justify-end mt-1">
                                <button wire:click="cancelEditAnswer" class="btn btn-ghost" style="font-size:0.75rem; padding:0.25rem 0.5rem;">Hủy</button>
                                <button wire:click="updateAnswer" class="btn btn-primary" style="font-size:0.75rem; padding:0.25rem 0.5rem;">Lưu</button>
                            </div>
                        </div>
                        @else
                        <p style="color:#2E2E2E; font-size:0.8rem; line-height:1.5; white-space:pre-line;">{{ $a->body }}</p>
                        @endif
                    </div>
                </div>
                @endforeach

                {{-- Reply form --}}
                @auth
                <div class="flex gap-2 mt-3">
                    <img src="{{ auth()->user()->avatar_url }}" class="avatar w-8 h-8 shrink-0" alt="">
                    <div style="flex:1;">
                        <textarea wire:model="answerBody" class="input" rows="2" placeholder="Viết câu trả lời..." style="font-size:0.8rem;"></textarea>
                        @error('answerBody') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
                        <div class="flex justify-end mt-1">
                            <button wire:click="submitAnswer" wire:loading.attr="disabled" class="btn btn-primary" style="font-size:0.8rem; padding:0.3rem 0.75rem;">
                                <span wire:loading.remove wire:target="submitAnswer">Trả lời</span>
                                <span wire:loading wire:target="submitAnswer">Đang gửi...</span>
                            </button>
                        </div>
                    </div>
                </div>
                @endauth
            </div>
            @endif
        </div>
        @empty
        <div class="card empty-state">
            <p style="font-size:2rem; margin-bottom:0.5rem;">💬</p>
            <p style="color:#5C5C66;">Chưa có câu hỏi nào</p>
        </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $questions->links() }}</div>
</div>
