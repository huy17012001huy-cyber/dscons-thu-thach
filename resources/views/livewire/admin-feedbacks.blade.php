<div class="admin-feedbacks-page">
<style>
    .admin-feedbacks-page { max-width: 920px; margin: 0 auto; }
    .admin-feedbacks-page .card { border-radius: 16px; border-color: #D7E5EA; }
    .admin-feedbacks-page .input:focus { border-color: #1F77BE; box-shadow: 0 0 0 3px rgba(31,119,190,.14); }
</style>

    <h1 style="font-size:1.25rem; font-weight:800; color:#1A1A1A; margin-bottom:1rem;">📬 Góp ý & Khiếu nại</h1>

    {{-- Filters --}}
    <div style="display:flex; gap:0.75rem; margin-bottom:1rem; flex-wrap:wrap;">
        <select wire:model.live="filterStatus" class="input" style="width:auto; font-size:0.8rem; padding:0.35rem 0.625rem;">
            <option value="">Tất cả trạng thái</option>
            <option value="pending">Chờ xử lý</option>
            <option value="reviewed">Đã xem</option>
            <option value="resolved">Đã giải quyết</option>
        </select>
        <select wire:model.live="filterType" class="input" style="width:auto; font-size:0.8rem; padding:0.35rem 0.625rem;">
            <option value="">Tất cả loại</option>
            <option value="gop_y">Góp ý</option>
            <option value="khieu_nai">Khiếu nại</option>
            <option value="bao_loi">Báo lỗi</option>
            <option value="thanh_toan">Thanh toán</option>
            <option value="khac">Khác</option>
        </select>
    </div>

    {{-- List --}}
    @forelse($feedbacks as $fb)
    <div class="card mb-3" x-data="{ showNotes: false }">
        <div style="display:flex; align-items:start; gap:0.75rem;">
            <img src="{{ $fb->user->avatar_url }}" class="avatar" style="width:32px; height:32px; flex-shrink:0;" alt="">
            <div style="flex:1; min-width:0;">
                <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                    <strong style="font-size:0.8rem; color:var(--color-text-primary);">{{ $fb->user->name }}</strong>
                    <span style="font-size:0.65rem; padding:0.15rem 0.4rem; border-radius:9999px; font-weight:600;
                        {{ match($fb->type) {
                            'khieu_nai' => 'background:#FEE2E2; color:#991B1B;',
                            'bao_loi' => 'background:#FFF7E6; color:#9A6700;',
                            'thanh_toan' => 'background:#EAF4FC; color:#125A96;',
                            'khac' => 'background:#F1F5F9; color:#475569;',
                            default => 'background:#DBEAFE; color:#1E40AF;',
                        } }}">
                        {{ ['khieu_nai' => 'Khiếu nại', 'bao_loi' => 'Báo lỗi', 'thanh_toan' => 'Thanh toán', 'khac' => 'Khác', 'gop_y' => 'Góp ý'][$fb->type] ?? $fb->type }}
                    </span>
                    <span style="font-size:0.65rem; padding:0.15rem 0.4rem; border-radius:9999px; font-weight:600;
                        {{ $fb->status === 'pending' ? 'background:#FEF3C7; color:#92400E;' : ($fb->status === 'reviewed' ? 'background:#DBEAFE; color:#1E40AF;' : 'background:#D1FAE5; color:#065F46;') }}">
                        {{ $fb->status === 'pending' ? 'Chờ xử lý' : ($fb->status === 'reviewed' ? 'Đã xem' : 'Đã giải quyết') }}
                    </span>
                </div>
                <p style="font-size:0.85rem; font-weight:600; color:var(--color-text-primary); margin:0.35rem 0 0.2rem;">{{ $fb->subject }}</p>
                <p style="font-size:0.8rem; color:var(--color-text-secondary); line-height:1.5; white-space:pre-line;">{{ $fb->content }}</p>
                <p style="font-size:0.65rem; color:var(--color-text-muted); margin-top:0.35rem;">{{ $fb->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</p>

                @if($fb->brand)
                <p style="font-size:0.68rem; color:#1F77BE; margin-top:0.25rem;">Cộng đồng: {{ $fb->brand->name }}</p>
                @endif

                @if($fb->attachments)
                <div style="display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.5rem;">
                    @foreach($fb->attachments as $attachment)
                    <a href="{{ asset('storage/'.$attachment) }}" target="_blank" rel="noopener" aria-label="Mở ảnh đính kèm">
                        <img src="{{ asset('storage/'.$attachment) }}" alt="Ảnh đính kèm" style="width:64px;height:48px;object-fit:cover;border:1px solid #D7E5EA;border-radius:6px;">
                    </a>
                    @endforeach
                </div>
                @endif

                @if($fb->admin_notes)
                <div style="margin-top:0.5rem; padding:0.5rem; background:#F7F5F3; border-radius:0.375rem; border:1px solid var(--color-border);">
                    <p style="font-size:0.7rem; font-weight:600; color:var(--color-text-muted); margin-bottom:0.2rem;">Ghi chú admin:</p>
                    <p style="font-size:0.75rem; color:var(--color-text-secondary); white-space:pre-line;">{{ $fb->admin_notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.5rem; margin-top:0.75rem; flex-wrap:wrap;">
            <button @click="showNotes = !showNotes" class="btn btn-ghost" style="font-size:0.75rem; padding:0.3rem 0.625rem;">
                Ghi chú
            </button>
            @if($fb->status === 'pending')
            <button wire:click="markReviewed({{ $fb->id }})" class="btn btn-secondary" style="font-size:0.75rem; padding:0.3rem 0.625rem;">Đã xem</button>
            @endif
            @if($fb->status !== 'resolved')
            <button wire:click="markResolved({{ $fb->id }})" class="btn btn-primary" style="font-size:0.75rem; padding:0.3rem 0.625rem;">Đã giải quyết</button>
            @endif
            <button wire:click="deleteFeedback({{ $fb->id }})" wire:confirm="Xóa feedback này?" class="btn btn-danger" style="font-size:0.75rem; padding:0.3rem 0.625rem;">Xóa</button>
        </div>

        {{-- Admin Notes Input --}}
        <div x-show="showNotes" x-transition style="margin-top:0.75rem;" x-data="{ notes: @js($fb->admin_notes ?? '') }">
            <textarea x-model="notes" class="input" placeholder="Ghi chú nội bộ..."
                      style="width:100%; font-size:0.8rem; min-height:60px; overflow:hidden; resize:none;"
                      x-init="$el.style.height = $el.scrollHeight + 'px'"
                      @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"></textarea>
            <button @click="$wire.saveNotes({{ $fb->id }}, notes); showNotes = false"
                    class="btn btn-primary" style="font-size:0.75rem; padding:0.3rem 0.625rem; margin-top:0.35rem;">
                Lưu ghi chú
            </button>
        </div>
    </div>
    @empty
    <div class="card text-center py-8">
        <p style="color:var(--color-text-muted);">Không có feedback nào.</p>
    </div>
    @endforelse

    {{-- Pagination --}}
    <div style="margin-top:1rem;">
        {{ $feedbacks->links() }}
    </div>
</div>
