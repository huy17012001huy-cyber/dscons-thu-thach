<div class="feedback-widget">
    {{-- Floating Action Button --}}
    @if(!$showModal)
    <button wire:click="openModal"
             type="button"
             class="feedback-fab"
             style="min-height:44px; border-radius:22px;
                    background:#fff; color:#125A96; border:1px solid #B8D7E6;
                    box-shadow:0 4px 14px rgba(18,59,89,0.12);
                   cursor:pointer; display:flex; align-items:center; gap:6px;
                   padding:0 14px 0 12px;
                   font-size:0.75rem; font-weight:600;
                   transition:background-color .2s ease, transform .2s ease, box-shadow .2s ease;"
            onmouseover="this.style.background='#EAF7FA'; this.style.transform='translateY(-1px)'"
            onmouseout="this.style.background='#fff'; this.style.transform='translateY(0)'">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
        </svg>
        Góp ý
    </button>
    @endif

    {{-- Modal Overlay --}}
    @if($showModal)
    <div style="position:fixed; inset:0; z-index:1001; display:flex; align-items:center; justify-content:center; padding:1rem;"
         x-data x-transition x-on:keydown.escape.window="$wire.closeModal()">
        {{-- Backdrop --}}
        <div wire:click="closeModal"
             style="position:absolute; inset:0; background:rgba(16,42,59,0.52);"></div>

        {{-- Modal --}}
        <div role="dialog" aria-modal="true" aria-labelledby="feedback-dialog-title" tabindex="-1" x-init="$nextTick(() => $el.querySelector('input, textarea, button')?.focus())" style="position:relative; background:#FFFFFF; border-radius:16px;
                    width:100%; max-width:440px; max-height:85vh; overflow-y:auto;
                    box-shadow:0 24px 48px rgba(0,0,0,0.2); padding:1.5rem 1.5rem 1.25rem;">

            {{-- Header --}}
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:10px; background:var(--color-accent-light);
                                display:flex; align-items:center; justify-content:center;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                        </svg>
                    </div>
                    <h3 id="feedback-dialog-title" style="margin:0; font-size:1rem; font-weight:700; color:var(--color-text-primary);">
                        Góp ý & Khiếu nại
                    </h3>
                </div>
                <button type="button" wire:click="closeModal" aria-label="Đóng góp ý và khiếu nại"
                        style="background:none; border:none; cursor:pointer; color:var(--color-text-muted); padding:4px;
                               border-radius:8px; transition:background 0.15s;"
                        onmouseover="this.style.background='var(--color-bg-muted)'"
                        onmouseout="this.style.background='none'">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <form wire:submit="submit">
                {{-- Type --}}
                <div style="margin-bottom:1rem;">
                    <div style="display:flex; gap:0.5rem;">
                        <label style="flex:1; display:flex; align-items:center; justify-content:center; gap:6px;
                                      padding:0.5rem; border-radius:10px; cursor:pointer;
                                       font-size:0.8rem; font-weight:600; transition:background-color .15s ease,color .15s ease,border-color .15s ease;
                                      {{ $type === 'gop_y' ? 'background:var(--color-accent-light); color:var(--color-accent); border:1.5px solid var(--color-accent);' : 'background:var(--color-bg-subtle); color:var(--color-text-muted); border:1.5px solid var(--color-border);' }}">
                            <input id="feedback-type-gop-y" type="radio" wire:model.live="type" value="gop_y" class="sr-only">
                            Góp ý
                        </label>
                        <label style="flex:1; display:flex; align-items:center; justify-content:center; gap:6px;
                                      padding:0.5rem; border-radius:10px; cursor:pointer;
                                       font-size:0.8rem; font-weight:600; transition:background-color .15s ease,color .15s ease,border-color .15s ease;
                                      {{ $type === 'khieu_nai' ? 'background:#FEF3C7; color:#92400E; border:1.5px solid #F59E0B;' : 'background:var(--color-bg-subtle); color:var(--color-text-muted); border:1.5px solid var(--color-border);' }}">
                            <input id="feedback-type-khieu-nai" type="radio" wire:model.live="type" value="khieu_nai" class="sr-only">
                            Khiếu nại
                        </label>
                    </div>
                    @error('type') <div role="alert" style="color:#DC2626; font-size:0.75rem; margin-top:0.25rem;">{{ $message }}</div> @enderror
                </div>

                {{-- Subject --}}
                <div style="margin-bottom:0.875rem;">
                    <label for="feedback-subject" style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-text-muted); margin-bottom:0.35rem;">Tiêu đề</label>
                    <input id="feedback-subject" type="text" wire:model="subject" class="input" placeholder="Tóm tắt vấn đề..."
                           style="width:100%; font-size:0.85rem;" @error('subject') aria-invalid="true" aria-describedby="feedback-subject-error" @enderror>
                    @error('subject') <div id="feedback-subject-error" role="alert" style="color:#DC2626; font-size:0.75rem; margin-top:0.25rem;">{{ $message }}</div> @enderror
                </div>

                {{-- Content --}}
                <div style="margin-bottom:1.25rem;">
                    <label for="feedback-content" style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-text-muted); margin-bottom:0.35rem;">Nội dung</label>
                    <textarea id="feedback-content" wire:model="content" class="input"
                              placeholder="Mô tả chi tiết, có thể kèm theo link Drive các vấn đề muốn khiếu nại/feedback..."
                              x-data
                              x-init="$el.style.height = $el.scrollHeight + 'px'"
                              @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                               style="width:100%; min-height:100px; font-size:0.85rem; overflow:hidden; resize:none; line-height:1.5;" @error('content') aria-invalid="true" aria-describedby="feedback-content-error" @enderror></textarea>
                    @error('content') <div id="feedback-content-error" role="alert" style="color:#DC2626; font-size:0.75rem; margin-top:0.25rem;">{{ $message }}</div> @enderror
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn btn-primary"
                        style="width:100%; justify-content:center; border-radius:10px; padding:0.625rem;"
                        wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove>Gửi</span>
                    <span wire:loading>Đang gửi...</span>
                </button>
            </form>
        </div>
    </div>
    @endif
</div>
