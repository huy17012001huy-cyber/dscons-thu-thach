<div class="orders-page ds-learning-page">
    <style>
        .orders-page { --orders-blue:#1F77BE; --orders-ink:#123B59; --orders-muted:#61798A; max-width:1120px; margin:0 auto; font-family:'Inter',ui-sans-serif,system-ui,sans-serif; }.orders-page .page-heading h1 { display:flex; align-items:center; gap:8px; }
        .orders-page .page-heading { margin-bottom:18px; }.orders-page .page-heading h1 { flex:0 0 auto; margin:0; color:var(--orders-ink); font-size:clamp(1.45rem,2vw,1.85rem); line-height:1.15; letter-spacing:-.035em; white-space:nowrap; }.orders-page .page-heading p { min-width:0; margin:.45rem 0 0; color:var(--orders-muted); font-size:.86rem; line-height:1.55; text-align:right; }
        .orders-membership { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:18px; padding:18px; border:1px solid #B8D7E6; border-radius:18px; background:linear-gradient(135deg,#F7FCFD,#EAF5FB); }.orders-membership-label { display:inline-flex; align-items:center; gap:6px; color:var(--orders-muted); font-size:.68rem; font-weight:800; letter-spacing:.065em; line-height:1.2; text-transform:uppercase; }.orders-membership h2 { display:flex; align-items:center; gap:7px; margin:5px 0 5px; color:var(--orders-ink); font-size:1rem; font-weight:800; line-height:1.3; letter-spacing:-.015em; }.orders-membership p { margin:0; color:var(--orders-muted); font-size:.78rem; line-height:1.5; }.orders-membership-actions { display:flex; flex-wrap:wrap; gap:8px; }.orders-cta { display:inline-flex; min-height:40px; align-items:center; justify-content:center; gap:6px; padding:0 12px; border:1px solid #1F77BE; border-radius:10px; color:#fff; background:#1F77BE; font-family:inherit; font-size:.72rem; font-weight:800; text-decoration:none; white-space:nowrap; }.orders-cta:hover { color:#fff; background:#125A96; }.orders-cta.is-secondary { color:#125A96; border-color:#B8D7E6; background:#fff; }
        .orders-tabs { display:flex; gap:5px; margin-bottom:18px; padding:5px; overflow-x:auto; border:1px solid #D7E5EE; border-radius:14px; background:#fff; scrollbar-width:none; }.orders-tabs::-webkit-scrollbar { display:none; }.orders-tab { display:inline-flex; align-items:center; gap:6px; min-height:40px; flex:0 0 auto; padding:0 12px; border:1px solid transparent; border-radius:10px; color:#61798A; background:transparent; font-size:.74rem; font-weight:750; cursor:pointer; }.orders-tab.is-active,.orders-tab:hover { color:#125A96; border-color:#B8D7E6; background:#E1F4F7; }
        .orders-list { display:grid; gap:10px; }.order-row { display:flex; align-items:center; gap:12px; padding:15px; border:1px solid #D7E5EE; border-radius:14px; background:#fff; }.order-icon { display:grid; place-items:center; width:38px; height:38px; flex:0 0 auto; border-radius:11px; color:#125A96; background:#E1F4F7; }.order-main { min-width:0; flex:1; }.order-main h3 { overflow:hidden; margin:0; color:var(--orders-ink); font-size:.9rem; font-weight:750; line-height:1.35; letter-spacing:-.01em; text-overflow:ellipsis; white-space:nowrap; }.order-main p { display:inline-flex; align-items:center; gap:5px; margin:4px 0 0; color:var(--orders-muted); font-size:.72rem; line-height:1.45; }.order-side { display:flex; align-items:flex-end; flex-direction:column; gap:6px; text-align:right; }.order-amount { display:inline-flex; align-items:center; gap:4px; color:#F39402; font-size:.78rem; font-weight:850; }.order-status { display:inline-flex; min-height:24px; align-items:center; gap:4px; padding:0 7px; border:1px solid #B8D7E6; border-radius:999px; color:#125A96; background:#EAF5FB; font-size:.66rem; font-weight:800; }.order-status.pending { color:#9A5B00; border-color:#F3D596; background:#FFF5DF; }.order-status.expired { color:#9B3D31; border-color:#F0C0B8; background:#FFF0EE; }.order-link { display:inline-flex; align-items:center; gap:4px; color:#125A96; font-size:.7rem; font-weight:800; text-decoration:none; }.orders-empty { padding:35px 20px; border:1px dashed #B8D7E6; border-radius:14px; color:var(--orders-muted); background:#F7FCFD; text-align:center; }.orders-empty strong { display:block; margin-bottom:5px; color:var(--orders-ink); font-size:.95rem; }.unowned-list { display:grid; grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); gap:10px; }.unowned-card { display:flex; align-items:center; justify-content:space-between; gap:8px; padding:12px; border:1px solid #D7E5EE; border-radius:12px; background:#fff; }.unowned-card strong { display:block; color:var(--orders-ink); font-size:.8rem; font-weight:750; }.unowned-card span { display:block; margin-top:3px; color:var(--orders-muted); font-size:.7rem; }.unowned-card a { display:inline-flex; align-items:center; gap:4px; color:#125A96; font-size:.7rem; font-weight:800; text-decoration:none; white-space:nowrap; }
        @media (max-width:760px) { .orders-page .page-heading { align-items:flex-start; flex-direction:column; gap:3px; }.orders-page .page-heading p { margin:0; text-align:left; } }
        @media (max-width:640px) { .orders-membership { align-items:stretch; flex-direction:column; }.orders-membership-actions .orders-cta { flex:1; }.order-row { align-items:flex-start; }.order-side { align-items:flex-start; text-align:left; }.order-main h3 { white-space:normal; }.unowned-list { grid-template-columns:1fr; } }
    </style>

    <div class="page-heading"><h1><x-icon name="receipt" size="25" />Gói & Đơn hàng</h1><p>Theo dõi membership, nội dung đã mua và những chương trình bạn có thể bắt đầu tiếp theo.</p></div>

    <section class="orders-membership">
        <div>
            <span class="orders-membership-label"><x-icon name="award" size="15" />Membership hiện tại</span>
            <h2><x-icon name="spark" size="20" />{{ ucfirst($membership?->tier ?: 'Free') }}</h2>
            <p>
                @if($membership?->isActive())
                    Đang hoạt động · {{ $membership->expires_at ? 'Hết hạn '.$membership->expires_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y') : 'Không giới hạn' }}
                @elseif($membership?->status === 'expired') Đã hết hạn · Hãy gia hạn để tiếp tục học tập
                @else Chưa có membership Premium trong community này
                @endif
            </p>
        </div>
        <div class="orders-membership-actions"><a href="{{ community_route('membership') }}" class="orders-cta"><x-icon name="refresh" size="16" />{{ $membership?->isActive() ? 'Gia hạn / đổi gói' : 'Xem các gói' }}</a><a href="{{ community_route('membership') }}" class="orders-cta is-secondary"><x-icon name="spark" size="16" />Quyền lợi membership</a></div>
    </section>

    <nav class="orders-tabs" aria-label="Bộ lọc đơn hàng">
        @foreach(['overview'=>['Tổng quan','grid'],'purchased'=>['Đã mua','check-circle'],'unowned'=>['Chưa mua','compass'],'pending'=>['Đang chờ','clock'],'expired'=>['Đã hết hạn','archive']] as $key => [$label,$icon])
            <button type="button" wire:click="setTab('{{ $key }}')" class="orders-tab {{ $tab === $key ? 'is-active' : '' }}"><x-icon name="{{ $icon }}" size="15" />{{ $label }} @if($key === 'purchased') ({{ $purchasedCount }}) @elseif($key === 'pending') ({{ $pendingCount }}) @endif</button>
        @endforeach
    </nav>

    @if($tab === 'unowned')
        @if($unowned->isNotEmpty())
            <div class="unowned-list">@foreach($unowned as $item)<div class="unowned-card"><div><strong>{{ $item->title }}</strong><span>{{ $item->kind_label }} · {{ $item->price > 0 ? number_format($item->price,0,',','.').'đ' : 'Miễn phí' }}</span></div><a href="{{ $item->url }}">{{ $item->action }} →</a></div>@endforeach</div>
        @else
            <div class="orders-empty"><strong>Bạn đã mở toàn bộ nội dung phù hợp</strong><span>Hãy tiếp tục hoàn thành các chương trình đang học.</span></div>
        @endif
    @else
        <div class="orders-list">
            @forelse($orders as $order)
                <div class="order-row">
                    <span class="order-icon"><x-icon name="{{ $order->kind === 'membership' ? 'award' : ($order->kind === 'course' ? 'graduation' : ($order->kind === 'challenge' ? 'target' : 'layers')) }}" size="19" /></span>
                    <div class="order-main"><h3>{{ $order->title }}</h3><p><x-icon name="calendar" size="13" />{{ $order->kind_label }} · {{ $order->date?->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }} @if($order->ref) · {{ $order->ref }} @endif</p></div>
                    <div class="order-side"><span class="order-amount"><x-icon name="tag" size="14" />{{ $order->amount > 0 ? number_format($order->amount,0,',','.').'đ' : 'Miễn phí' }}</span><span class="order-status {{ in_array($order->status, ['pending'], true) ? 'pending' : (in_array($order->status, ['expired','rejected','banned'], true) ? 'expired' : '') }}"><x-icon name="{{ in_array($order->status, ['pending'], true) ? 'clock' : (in_array($order->status, ['expired','rejected','banned'], true) ? 'archive' : 'check-circle') }}" size="13" />{{ $order->status_label }}</span>@if($order->url)<a class="order-link" href="{{ $order->url }}">{{ $order->action }} <x-icon name="arrow-right" size="14" /></a>@endif</div>
                </div>
            @empty
                <div class="orders-empty"><strong>Chưa có giao dịch nào</strong><span>Các gói, khóa học, Challenge và tài nguyên bạn tham gia sẽ xuất hiện ở đây.</span></div>
            @endforelse
        </div>
    @endif
</div>
