<div class="affiliate-page">
<style>
    .affiliate-page { width: min(100%, 880px); margin: 0 auto; }
    .affiliate-page .card { border-radius: 16px; border-color: #D7E5EA; }
    .affiliate-page .affiliate-stat { box-shadow: none; }
    .affiliate-page .affiliate-stat:hover { border-color: #A9CAD7; background: #FBFDFE; }
    .affiliate-page .input:focus { border-color: #1F77BE; box-shadow: 0 0 0 3px rgba(31,119,190,.14); }
    @media (max-width: 640px) { .affiliate-page .affiliate-link-row { align-items: stretch; } .affiliate-page .affiliate-link-row .btn { justify-content: center; } }
</style>

    <h1 style="font-size:1.25rem; font-weight:800; color:#1A1A1A; margin-bottom:0.25rem;"><x-icon name="user-plus" size="22" /> Affiliate</h1>
    <p style="font-size:0.8rem; color:#5C5C66; margin-bottom:1.5rem;">Giới thiệu bạn bè · Nhận hoa hồng 20%</p>

    {{-- Referral link --}}
    <div class="card mb-4">
        <h2 style="font-size:0.9rem; font-weight:700; color:#1A1A1A; margin-bottom:0.75rem;">Link giới thiệu của bạn</h2>
        <div class="affiliate-link-row flex gap-2" x-data="{ copied: false }">
            <input type="text" value="{{ $referralLink }}" readonly class="input" style="font-size:0.8rem; background:#F7F5F3;">
            <button @click="navigator.clipboard.writeText('{{ $referralLink }}'); copied = true; setTimeout(() => copied = false, 2000)"
                class="btn btn-primary shrink-0" style="font-size:0.8rem; padding:0.4rem 0.875rem;">
                <span x-show="!copied">Sao chép</span>
                <span x-show="copied" x-cloak>Đã chép ✓</span>
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid gap-4 mb-4" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="affiliate-stat card text-center">
            <p style="font-size:1.5rem; font-weight:800; color:#1A1A1A;">{{ $referralCount }}</p>
            <p style="font-size:0.8rem; color:#5C5C66;">Người đã giới thiệu</p>
        </div>
        <div class="affiliate-stat card text-center">
            <p style="font-size:1.5rem; font-weight:800; color:#d17856;">{{ number_format($totalPending) }}đ</p>
            <p style="font-size:0.8rem; color:#5C5C66;">Đang chờ thanh toán</p>
        </div>
        <div class="affiliate-stat card text-center">
            <p style="font-size:1.5rem; font-weight:800; color:#059669;">{{ number_format($totalEarned) }}đ</p>
            <p style="font-size:0.8rem; color:#5C5C66;">Đã nhận</p>
        </div>
    </div>

    {{-- Earnings history --}}
    <div class="card">
        <h2 style="font-size:0.9rem; font-weight:700; color:#1A1A1A; margin-bottom:0.75rem;">Lịch sử hoa hồng</h2>
        @forelse($earnings as $earning)
        <div class="flex items-center gap-3 py-3 {{ !$loop->last ? 'border-b' : '' }}" style="{{ !$loop->last ? 'border-color:#E1E1E1;' : '' }}">
            <img src="{{ $earning->referred->avatar_url }}" class="avatar w-8 h-8" alt="">
            <div style="flex:1;">
                <p style="font-size:0.8rem; font-weight:600; color:#1A1A1A;">{{ $earning->referred->name }}</p>
                <p style="font-size:0.7rem; color:#5C5C66;">{{ $earning->created_at->format('d/m/Y') }} · {{ round($earning->commission_rate * 100) }}% hoa hồng</p>
            </div>
            <div style="text-align:right;">
                <p style="font-size:0.875rem; font-weight:700; color:#d17856;">{{ number_format($earning->amount) }}đ</p>
                <span class="badge" style="font-size:0.65rem; {{ match($earning->status) { 'paid' => 'background:#D1FAE5; color:#065F46;', 'cancelled' => 'background:#FEE2E2; color:#991B1B;', default => 'background:#E8F5E9; color:#1B5E20;' } }}">
                    {{ match($earning->status) { 'paid' => 'Đã thanh toán', 'cancelled' => 'Đã hủy', default => 'Chờ xử lý' } }}
                </span>
            </div>
        </div>
        @empty
        <div class="text-center py-8">
            <p style="font-size:1.5rem; margin-bottom:0.5rem;"><x-icon name="user-plus" size="28" /></p>
            <p style="color:#5C5C66; font-size:0.85rem;">Chưa có hoa hồng nào. Chia sẻ link giới thiệu để bắt đầu!</p>
        </div>
        @endforelse
    </div>
</div>
