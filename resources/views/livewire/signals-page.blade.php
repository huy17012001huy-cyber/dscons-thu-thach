<div class="signals-page">
<style>
    .signals-page { width: min(100%, 960px); margin: 0 auto; }
    .signals-page > .card { border-radius: 16px; border-color: #D7E5EA; }
    .signals-page .signals-filter { padding: 5px; border: 1px solid #D7E5EA; border-radius: 14px; background: #fff; }
    .signals-page .signals-filter .badge { min-height: 38px; border-radius: 10px; transition: opacity .16s ease, transform .16s ease, box-shadow .16s ease; }
    .signals-page .signals-filter .badge:hover { opacity: 1 !important; transform: translateY(-1px); box-shadow: 0 3px 8px rgba(18,59,89,.12); }
    .signals-loading-state { display:flex; align-items:center; gap:8px; min-height:42px; margin:12px 0; padding:10px 12px; border:1px solid #D7E5EA; border-radius:12px; background:#F8FCFD; color:#61798A; font-size:.78rem; }
    .signals-empty-cta { display:inline-flex; min-height:40px; align-items:center; margin-top:.55rem; padding:.55rem .8rem; border:1px solid #B8D7E6; border-radius:10px; color:#125A96; font-size:.78rem; font-weight:750; text-decoration:none; background:#EAF7FA; }
    .signals-empty-cta:hover { border-color:#1F77BE; background:#D9F0F5; }
    @media (max-width: 640px) { .signals-page .signals-filter { flex-wrap: nowrap; overflow-x: auto; scrollbar-width:none; } .signals-page .signals-filter::-webkit-scrollbar { display:none; } }
</style>
@php $pillars = brand()->pillarProfiles(); @endphp

    <div class="card mb-4" style="background:#E7F0F5; border-color:#2B7BBB;">
        <div class="flex items-center gap-3">
            <span class="section-mark" aria-hidden="true">R</span>
            <div>
                <h1 style="font-size:1.25rem; font-weight:800; color:#0F3D5E;">Tín hiệu công nghệ</h1>
                <p style="font-size:0.8rem; color:#61798A;">Cập nhật công cụ, tiêu chuẩn và workflow cho kỹ sư MEP/BIM · <strong style="color:#2B7BBB;">{{ $todayCount }} cập nhật hôm nay</strong></p>
            </div>
        </div>
    </div>

    <div class="signals-filter flex flex-wrap gap-2 mb-4">
        @foreach($pillars as $p => $pillarData)
        <button type="button" wire:click="setPillar('{{ $p }}')" aria-pressed="{{ $pillar === $p ? 'true' : 'false' }}" class="badge badge-pillar-{{ $p }}" style="cursor:pointer; padding:0.3rem 0.75rem; font-size:0.75rem; {{ $pillar === $p ? '' : 'opacity:0.5;' }}"><x-icon name="{{ $pillarData['icon'] }}" size="13" color="{{ $pillarData['color'] }}" /> {{ $pillarData['name'] }}</button>
        @endforeach
    </div>

    <div wire:loading.flex wire:target="setPillar,gotoPage" class="signals-loading-state" role="status" aria-live="polite">
        <span class="loading-dot" aria-hidden="true"></span> Đang cập nhật tín hiệu...
    </div>

    <div class="flex flex-col gap-3">
        @forelse($posts as $post)
        <livewire:post-card :post="$post" :key="'sig-'.$post->id" />
        @empty
        <div class="card empty-state">
            <span class="section-mark" aria-hidden="true">R</span>
            <p style="color:#5C5C66;">Chưa có tín hiệu nào{{ $pillar ? ' trong trụ cột này' : '' }}</p>
            <a href="{{ route('feed') }}" class="signals-empty-cta">Mở bảng tin để chia sẻ</a>
        </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $posts->links() }}</div>
</div>
