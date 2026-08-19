<div>
    <div class="card mb-4" style="background:#E7F0F5; border-color:#2B7BBB;">
        <div class="flex items-center gap-3">
            <span style="font-size:2.5rem;">★</span>
            <div>
                <h1 style="font-size:1.25rem; font-weight:800; color:#0F3D5E;">Kiến thức cốt lõi</h1>
                <p style="font-size:0.8rem; color:#61798A;">Thư viện thực chiến về MEP, BIM, AI và Vibe Coding do cộng đồng chọn lọc.</p>
            </div>
        </div>
    </div>

    <div class="card mb-4" style="padding:0.875rem;">
        <div class="flex flex-wrap gap-3 items-center">
            <input wire:model.live.debounce.300="search" type="search" class="input" style="max-width:220px;" placeholder="Tìm kiến thức cốt lõi...">
            <div class="flex gap-1">
                @foreach(['offer'=>'🔥','traffic'=>'✨','conversion'=>'🎯','delivery'=>'⚙️','continuity'=>'🔗'] as $p => $e)
                <button wire:click="setPillar('{{ $p }}')" class="badge badge-pillar-{{ $p }}" style="cursor:pointer; {{ $pillar === $p ? '' : 'opacity:0.45;' }} padding:0.25rem 0.5rem;">{{ $e }}</button>
                @endforeach
            </div>
            <div class="flex gap-2 ml-auto">
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
            <p style="font-size:2rem; margin-bottom:0.5rem;">★</p>
            <p style="color:#5C5C66;">Chưa có bài CỐT nào{{ $search ? ' khớp với tìm kiếm' : '' }}{{ $pillar ? ' trong trụ cột này' : '' }}</p>
        </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $posts->links() }}</div>
</div>
