<div>
    <div class="card mb-4" style="background:#E7F0F5; border-color:#2B7BBB;">
        <div class="flex items-center gap-3">
            <span style="font-size:2.5rem;">⚡</span>
            <div>
                <h1 style="font-size:1.25rem; font-weight:800; color:#0F3D5E;">Tín hiệu công nghệ</h1>
                <p style="font-size:0.8rem; color:#61798A;">Radar AI, BIM và tự động hóa cho kỹ sư MEP · <strong style="color:#2B7BBB;">{{ $todayCount }} tín hiệu hôm nay</strong></p>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap gap-2 mb-4">
        @foreach(['offer'=>['🔥','Offer'],'traffic'=>['✨','Thu hút'],'conversion'=>['🎯','Chuyển đổi'],'delivery'=>['⚙️','Cung ứng'],'continuity'=>['🔗','Continuity']] as $p => [$e,$l])
        <button wire:click="setPillar('{{ $p }}')" class="badge badge-pillar-{{ $p }}" style="cursor:pointer; padding:0.3rem 0.75rem; font-size:0.75rem; {{ $pillar === $p ? '' : 'opacity:0.5;' }}">{{ $e }} {{ $l }}</button>
        @endforeach
    </div>

    <div class="flex flex-col gap-3">
        @forelse($posts as $post)
        <livewire:post-card :post="$post" :key="'sig-'.$post->id" />
        @empty
        <div class="card text-center py-12">
            <p style="font-size:2rem; margin-bottom:0.5rem;">⚡</p>
            <p style="color:#5C5C66;">Chưa có tín hiệu nào{{ $pillar ? ' trong trụ cột này' : '' }}</p>
        </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $posts->links() }}</div>
</div>
