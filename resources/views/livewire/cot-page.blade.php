<div class="cot-page">
<style>
    .cot-page { width: min(100%, 960px); margin: 0 auto; }
    .cot-hero { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1rem; padding:1.1rem 1.2rem; border:1px solid #9DC9E2; border-left:5px solid #1F77BE; border-radius:16px; background:linear-gradient(135deg,#F7FCFF 0%,#E7F3FA 100%); }
    .cot-hero-mark { display:grid; place-items:center; width:46px; height:46px; flex:0 0 auto; border:1px solid #9DC9E2; border-radius:13px; background:#fff; color:#125A96; }
    .cot-hero-eyebrow { display:block; margin-bottom:.2rem; color:#1F77BE; font-size:.65rem; font-weight:850; letter-spacing:.12em; text-transform:uppercase; }
    .cot-hero h1 { margin:0; color:#0F3D5E; font-size:1.25rem; font-weight:850; }
    .cot-hero p { margin:.25rem 0 0; color:#61798A; font-size:.8rem; }
    .cot-hero-count { display:grid; justify-items:end; flex:0 0 auto; color:#125A96; }
    .cot-hero-count strong { font-size:1.25rem; line-height:1; }
    .cot-hero-count span { margin-top:.25rem; color:#61798A; font-size:.68rem; }
    .cot-page .cot-toolbar { border-color: #D7E5EA; background: #fff; }
    .cot-page .cot-pillars .badge { min-width: 38px; min-height: 38px; justify-content: center; border-radius: 10px; transition: opacity .16s ease, transform .16s ease, box-shadow .16s ease; }
    .cot-page .cot-pillars .badge:hover { opacity: 1 !important; transform: translateY(-1px); box-shadow: 0 3px 8px rgba(18,59,89,.12); }
    @media (max-width: 640px) { .cot-page .cot-toolbar-row { align-items: stretch; } .cot-page .cot-sort { margin-left: 0; } .cot-hero { align-items:flex-start; padding:1rem; } .cot-hero-count { display:none; } }
</style>
@php $pillars = collect(brand()->pillarProfiles())->reject(fn ($pillarData, $pillar) => brand()->slug === 'dscons' && $pillar === 'continuity')->all(); @endphp

    <div class="cot-hero">
        <div class="cot-hero-mark" aria-hidden="true"><x-icon name="layers" size="22" /></div>
        <div style="min-width:0;flex:1;">
            <span class="cot-hero-eyebrow">DSCons library</span>
            <h1>Kiến thức cốt lõi</h1>
            <p>Thư viện thực chiến về MEP, BIM và quy trình triển khai do cộng đồng chọn lọc.</p>
        </div>
        <div class="cot-hero-count"><strong>{{ $posts->total() }}</strong><span>bài chọn lọc</span></div>
    </div>

    <div class="cot-toolbar card mb-4" style="padding:0.875rem;">
        <div class="cot-toolbar-row flex flex-wrap gap-3 items-center">
            <input wire:model.live.debounce.300="search" type="search" class="input" style="max-width:220px;" placeholder="Tìm kiến thức cốt lõi...">
            <div class="cot-pillars flex gap-1">
                @foreach($pillars as $p => $pillarData)
                <button wire:click="setPillar('{{ $p }}')" class="badge badge-pillar-{{ $p }}" style="cursor:pointer; {{ $pillar === $p ? '' : 'opacity:0.45;' }} padding:0.25rem 0.5rem;"><x-icon name="{{ $pillarData['icon'] }}" size="13" color="{{ $pillarData['color'] }}" /> {{ $pillarData['name'] }}</button>
                @endforeach
            </div>
            <div class="cot-sort flex gap-2 ml-auto">
                <button wire:click="setSort('latest')" class="btn btn-ghost" style="font-size:0.75rem; padding:0.25rem 0.625rem; {{ $sort === 'latest' ? 'color:#1A1A1A; font-weight:600;' : '' }}">Mới nhất</button>
                <button wire:click="setSort('popular')" class="btn btn-ghost" style="font-size:0.75rem; padding:0.25rem 0.625rem; {{ $sort === 'popular' ? 'color:#1A1A1A; font-weight:600;' : '' }}">Phổ biến</button>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-3">
        @forelse($posts as $post)
        <livewire:post-card :post="$post" :key="'cot-'.$post->id" />
        @empty
        <div class="card empty-state">
            <span class="section-mark" aria-hidden="true">CỐT</span>
            <p style="color:#5C5C66;">Chưa có bài CỐT nào{{ $search ? ' khớp với tìm kiếm' : '' }}{{ $pillar ? ' trong trụ cột này' : '' }}</p>
        </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $posts->links() }}</div>
</div>
