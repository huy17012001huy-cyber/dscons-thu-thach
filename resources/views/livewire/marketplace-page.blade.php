<div class="marketplace-page ds-learning-page">
    <style>
        .marketplace-page { --market-blue:#1F77BE; --market-ink:#123B59; --market-muted:#61798A; max-width:1120px; margin:0 auto; }
        .marketplace-page .page-heading { margin-bottom:18px; }
        .marketplace-page .page-heading h1 { display:flex; align-items:center; gap:8px; margin:0; color:var(--market-ink); font-size:clamp(1.45rem,2vw,1.85rem); line-height:1.15; letter-spacing:-.035em; }
        .marketplace-page .page-heading p { margin:.45rem 0 0; color:var(--market-muted); font-size:.86rem; line-height:1.55; }
        .marketplace-section { margin-bottom:30px; }
        .marketplace-section-head { display:flex; align-items:flex-end; justify-content:space-between; gap:12px; margin-bottom:11px; }
        .marketplace-section-head h2 { display:inline-flex; align-items:center; gap:7px; margin:0; color:var(--market-ink); font-size:1.08rem; letter-spacing:-.02em; }
        .marketplace-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:14px; }
        .marketplace-item { display:flex; min-width:0; flex-direction:column; overflow:hidden; border:1px solid #D7E5EE; border-radius:16px; background:#fff; box-shadow:0 3px 10px rgba(18,59,89,.04); transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease; }
        .marketplace-item:hover { transform:translateY(-3px); border-color:#8FB9CB; box-shadow:0 13px 26px rgba(18,59,89,.1); }
        .marketplace-item-cover { position:relative; display:block; aspect-ratio:16/9; overflow:hidden; background:linear-gradient(135deg,#E1F4F7,#D6E9F4); text-decoration:none; }
        .marketplace-item-cover img { width:100%; height:100%; object-fit:cover; transition:transform .35s ease; }
        .marketplace-item:hover .marketplace-item-cover img { transform:scale(1.035); }
        .marketplace-item-fallback { display:grid; place-items:center; width:100%; height:100%; color:#125A96; font-size:1.15rem; font-weight:850; letter-spacing:.15em; }
        .marketplace-kind,.marketplace-featured { position:absolute; top:10px; padding:5px 7px; border-radius:999px; color:#fff; background:rgba(18,59,89,.78); font-size:.62rem; font-weight:800; backdrop-filter:blur(6px); }
        .marketplace-kind { left:10px; }.marketplace-featured { right:10px; color:#fff; background:#F39402; }
        .marketplace-item-body { display:flex; flex:1; flex-direction:column; padding:13px; }
        .marketplace-item-tags { display:flex; min-height:25px; flex-wrap:wrap; gap:5px; margin-bottom:8px; }
        .marketplace-tag { display:inline-flex; min-height:23px; align-items:center; gap:4px; padding:0 7px; border:1px solid #D7E5EE; border-radius:999px; color:#456477; background:#F7FAFC; font-size:.62rem; font-weight:750; }
        .marketplace-tag.is-blue { color:#125A96; border-color:#B8D7E6; background:#EAF5FB; }.marketplace-tag.is-owned { color:#125A96; border-color:#B8D7E6; background:#E1F4F7; }.marketplace-tag.is-pending { color:#9A5B00; border-color:#F3D596; background:#FFF5DF; }.marketplace-tag.is-difficulty-normal { color:#18794E; border-color:#A7D8BE; background:#EAF8F0; }.marketplace-tag.is-difficulty-hard { color:#9A5B00; border-color:#F3D596; background:#FFF5DF; }.marketplace-tag.is-difficulty-chaos { color:#9B3D31; border-color:#F0C0B8; background:#FFF0EE; }
        .marketplace-item h3 { margin:0; color:var(--market-ink); font-size:.94rem; line-height:1.35; }
        .marketplace-item p { min-height:38px; margin:7px 0 10px; color:var(--market-muted); font-size:.74rem; line-height:1.5; }
        .marketplace-item-meta { display:flex; justify-content:space-between; gap:8px; padding:9px 0; border-top:1px solid #E7EEF1; color:var(--market-muted); font-size:.66rem; }.marketplace-item-meta span,.marketplace-kind,.marketplace-featured,.marketplace-price,.marketplace-state { display:inline-flex; align-items:center; gap:4px; }
        .marketplace-item-footer { display:flex; align-items:center; justify-content:space-between; gap:8px; padding-top:10px; }
        .marketplace-price { color:#F39402; font-size:.85rem; font-weight:850; }.marketplace-price.is-free { color:#125A96; font-size:.75rem; }
        .marketplace-cta { display:inline-flex; min-height:38px; align-items:center; justify-content:center; padding:0 10px; border:1px solid #1F77BE; border-radius:9px; color:#fff; background:#1F77BE; font-size:.68rem; font-weight:800; text-decoration:none; white-space:nowrap; cursor:pointer; }
        .marketplace-cta:hover { color:#fff; background:#125A96; }.marketplace-state { font-size:.68rem; font-weight:800; }.marketplace-state.is-owned { color:#125A96; }.marketplace-state.is-pending { color:#9A5B00; }
        .marketplace-empty { padding:32px; border:1px dashed #B8D7E6; border-radius:14px; color:var(--market-muted); background:#F7FCFD; text-align:center; }
        @media (max-width:640px) { .marketplace-grid { grid-template-columns:1fr; }.marketplace-section-head { align-items:flex-start; flex-direction:column; gap:3px; } }
        @media (prefers-reduced-motion:reduce) { .marketplace-item,.marketplace-item-cover img { transition:none; } }
    </style>

    <div class="page-heading">
        <h1><x-icon name="shopping-cart" size="25" />Marketplace</h1>
        <p>Challenge, khóa học và tài nguyên được chọn lọc cho hành trình phát triển năng lực.</p>
    </div>

    @foreach([
        ['items' => $challengeItems, 'title' => 'Thử thách hot', 'icon' => 'target'],
        ['items' => $courseItems, 'title' => 'Khóa học', 'icon' => 'graduation'],
        ['items' => $resourceItems, 'title' => 'Nổi bật & trending tuần này', 'icon' => 'spark'],
    ] as $section)
        @if($section['items']->isNotEmpty())
            <section class="marketplace-section"><div class="marketplace-section-head"><h2><x-icon name="{{ $section['icon'] }}" size="19" />{{ $section['title'] }}</h2></div><div class="marketplace-grid">@foreach($section['items'] as $item) @include('livewire.partials.marketplace-item', ['item' => $item]) @endforeach</div></section>
        @endif
    @endforeach

    @if($items->isEmpty())
        <div class="marketplace-empty">Chưa có nội dung để hiển thị.</div>
    @endif
</div>
