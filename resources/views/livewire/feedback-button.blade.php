<div>
    {{-- Floating Action Button --}}
    @if(!$showModal)
    <button wire:click="openModal"
            style="position:fixed; bottom:6rem; right:1.5rem; z-index:1000;
                   height:40px; border-radius:20px;
                   background:#DC2626; color:#fff; border:none;
                   box-shadow:0 2px 10px rgba(220,38,38,0.35);
                   cursor:pointer; display:flex; align-items:center; gap:6px;
                   padding:0 14px 0 12px;
                   font-size:0.75rem; font-weight:600;
                   transition:all 0.2s;"
            onmouseover="this.style.background='#B91C1C'; this.style.transform='scale(1.04)'"
            onmouseout="this.style.background='#DC2626'; this.style.transform='scale(1)'">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
        </svg>
        Góp ý
    </button>
    @endif

    {{-- Modal Overlay --}}
    @if($showModal)
    <div style="position:fixed; inset:0; z-index:1001; display:flex; align-items:center; justify-content:center; padding:1rem;"
         x-data x-transition>
        {{-- Backdrop --}}
        <div wire:click="closeModal"
             style="position:absolute; inset:0; background:rgba(0,0,0,0.45); backdrop-filter:blur(2px);"></div>

        {{-- Modal --}}
        <div style="position:relative; background:#FFFFFF; border-radius:16px;
                    width:100%; max-width:440px; max-height:85vh; overflow-y:auto;
                    box-shadow:0 24px 48px rgba(0,0,0,0.2); padding:1.5rem 1.5rem 1.25rem;">

            {{-- Header --}}
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:10px; background:var(--color-accent-light);
                                display:flex; align-items:center; justify-content:center;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                        </svg>
                    </div>
                    <h3 style="margin:0; font-size:1rem; font-weight:700; color:var(--color-text-primary);">
                        Góp ý & Khiếu nại
                    </h3>
                </div>
                <button wire:click="closeModal"
                        style="background:none; border:none; cursor:pointer; color:var(--color-text-muted); padding:4px;
                               border-radius:8px; transition:background 0.15s;"
                        onmouseover="this.style.background='var(--color-bg-muted)'"
                        onmouseout="this.style.background='none'">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <form wire:submit="submit">
                {{-- Type --}}
                <div style="margin-bottom:1rem;">
                    <div style="display:flex; gap:0.5rem;">
                        <label style="flex:1; display:flex; align-items:center; justify-content:center; gap:6px;
                                      padding:0.5rem; border-radius:10px; cursor:pointer;
                                      font-size:0.8rem; font-weight:600; transition:all 0.15s;
                                      {{ $type === 'gop_y' ? 'background:var(--color-accent-light); color:var(--color-accent); border:1.5px solid var(--color-accent);' : 'background:var(--color-bg-subtle); color:var(--color-text-muted); border:1.5px solid var(--color-border);' }}">
                            <input type="radio" wire:model.live="type" value="gop_y" style="display:none;">
                            💬 Góp ý
                        </label>
                        <label style="flex:1; display:flex; align-items:center; justify-content:center; gap:6px;
                                      padding:0.5rem; border-radius:10px; cursor:pointer;
                                      font-size:0.8rem; font-weight:600; transition:all 0.15s;
                                      {{ $type === 'khieu_nai' ? 'background:#FEF3C7; color:#92400E; border:1.5px solid #F59E0B;' : 'background:var(--color-bg-subtle); color:var(--color-text-muted); border:1.5px solid var(--color-border);' }}">
                            <input type="radio" wire:model.live="type" value="khieu_nai" style="display:none;">
                            ⚠️ Khiếu nại
                        </label>
                    </div>
                    @error('type') <div style="color:#DC2626; font-size:0.75rem; margin-top:0.25rem;">{{ $message }}</div> @enderror
                </div>

                {{-- Subject --}}
                <div style="margin-bottom:0.875rem;">
                    <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-text-muted); margin-bottom:0.35rem;">Tiêu đề</label>
                    <input type="text" wire:model="subject" class="input" placeholder="Tóm tắt vấn đề..."
                           style="width:100%; font-size:0.85rem;">
                    @error('subject') <div style="color:#DC2626; font-size:0.75rem; margin-top:0.25rem;">{{ $message }}</div> @enderror
                </div>

                {{-- Content --}}
                <div style="margin-bottom:1.25rem;">
                    <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-text-muted); margin-bottom:0.35rem;">Nội dung</label>
                    <textarea wire:model="content" class="input"
                              placeholder="Mô tả chi tiết, có thể kèm theo link Drive các vấn đề muốn khiếu nại/feedback..."
                              x-data
                              x-init="$el.style.height = $el.scrollHeight + 'px'"
                              @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                              style="width:100%; min-height:100px; font-size:0.85rem; overflow:hidden; resize:none; line-height:1.5;"></textarea>
                    @error('content') <div style="color:#DC2626; font-size:0.75rem; margin-top:0.25rem;">{{ $message }}</div> @enderror
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn btn-primary"
                        style="width:100%; justify-content:center; border-radius:10px; padding:0.625rem;"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>Gửi</span>
                    <span wire:loading>Đang gửi...</span>
                </button>
            </form>
        </div>
    </div>
    @endif
</div>
