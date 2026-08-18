<div>
    {{-- Rune Banner --}}
    @if($activeRune)
    <div class="rune-banner flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <span style="font-size:1.25rem;">~</span>
            <div>
                <p style="font-weight:700; color:#C2410C; font-size:0.875rem;">Phù văn đang kích hoạt!</p>
                <p style="color:#5C5C66; font-size:0.75rem;">Comment đầu tiên vào bài của {{ $activeRune->user->name }} → nhận <strong style="color:#d17856;">2x EXP</strong></p>
            </div>
        </div>
        <a href="#post-{{ $activeRune->id }}" style="font-size:0.75rem; color:#C2410C; font-weight:600; white-space:nowrap;">
            Đến bài →
        </a>
    </div>
    @endif

    {{-- Compose Box (Lv10+ only) --}}
    @auth
    @if(auth()->user()->level >= 10)
    <livewire:compose-post />
    @else
    <div class="card mb-4" style="text-align:center; padding:1rem;">
        <p style="font-size:0.85rem; color:#5C5C66;">▪ Đạt <strong style="color:#d17856;">Level 10</strong> để mở khóa đăng bài. Hãy tương tác bằng comment!</p>
        <div class="xp-bar mt-2" style="height:6px; max-width:200px; margin:0.5rem auto 0;">
            <div class="xp-bar-fill" style="width:{{ min(100, auth()->user()->level * 10) }}%;"></div>
        </div>
        <p style="font-size:0.7rem; color:#5C5C66; margin-top:0.375rem;">Level {{ auth()->user()->level }}/10</p>
    </div>
    @endif
    @endauth

    {{-- Tab bar + Pillar dropdown --}}
    <div class="flex items-center gap-1 mt-4 mb-3 flex-wrap">
        <div class="tab-nav" style="margin-bottom:0; border:0; flex:1; min-width:0;">
            <button wire:click="setTab('latest')" class="tab-item {{ $tab === 'latest' ? 'active' : '' }}" style="white-space:nowrap;">Mới nhất</button>
            <button wire:click="setTab('cot')" class="tab-item {{ $tab === 'cot' ? 'active' : '' }}" style="white-space:nowrap;">★ CỐT</button>
            <button wire:click="setTab('popular')" class="tab-item {{ $tab === 'popular' ? 'active' : '' }}" style="white-space:nowrap;"> Phổ biến</button>
            <button wire:click="setTab('signal')" class="tab-item {{ $tab === 'signal' ? 'active' : '' }}" style="white-space:nowrap;">⚡ Tín hiệu</button>
        </div>

        {{-- Pillar dropdown --}}
        <div x-data="{ open: false }" style="position:relative;">
            <button @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="listbox" class="tab-item {{ $tab === 'pillar' ? 'active' : '' }}" style="white-space:nowrap; display:flex; align-items:center; gap:0.25rem;">
                @if($tab === 'pillar' && $pillar)
                    @php $pillarMap = ['offer'=>'🔥 Offer','traffic'=>'✨ Thu hút','conversion'=>'🎯 Chuyển đổi','delivery'=>'⚙️ Cung ứng','continuity'=>'🔗 Continuity']; @endphp
                    {{ $pillarMap[$pillar] ?? 'Trụ cột' }}
                @else
                    Trụ cột
                @endif
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" @click.away="open = false" x-transition
                 style="position:absolute; right:0; top:calc(100% + 6px); background:#FFF; border:1px solid #E1E1E1; border-radius:0.5rem; padding:0.375rem; min-width:160px; z-index:50; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                @foreach([
                    'offer'      => ['🔥', 'Offer',      'badge-pillar-offer'],
                    'traffic'    => ['✨', 'Thu hút',     'badge-pillar-traffic'],
                    'conversion' => ['🎯', 'Chuyển đổi', 'badge-pillar-conversion'],
                    'delivery'   => ['⚙️', 'Cung ứng',   'badge-pillar-delivery'],
                    'continuity' => ['🔗', 'Continuity',  'badge-pillar-continuity'],
                ] as $p => [$e, $l, $cls])
                <button wire:click="setPillar('{{ $p }}')" @click="open = false"
                    style="display:flex; align-items:center; gap:0.5rem; width:100%; text-align:left; padding:0.375rem 0.625rem; font-size:0.8rem; border-radius:0.25rem; cursor:pointer; {{ $tab === 'pillar' && $pillar === $p ? 'background:#E8F5E9; color:#1B5E20; font-weight:600;' : 'color:#2E2E2E;' }}">
                    <span>{{ $e }}</span> {{ $l }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Pinned posts --}}
    @foreach($pinnedPosts as $post)
    <div style="position:relative; margin-bottom:0.75rem;">
        <div style="position:absolute; top:0.75rem; right:0.75rem; display:flex; align-items:center; gap:0.25rem; font-size:0.65rem; color:#5C5C66;">
            <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
            Ghim
        </div>
        <livewire:post-card :post="$post" :key="'pin-'.$post->id" />
    </div>
    @endforeach

    {{-- Posts --}}
    <div style="display:flex; flex-direction:column; gap:8px;">
        @forelse($posts as $post)
            <livewire:post-card :post="$post" :key="'p-'.$post->id" />
        @empty
            <div class="card text-center py-12">
                <p style="font-size:2rem; margin-bottom:0.5rem;">📭</p>
                <p style="color:#5C5C66;">Chưa có bài viết nào</p>
                @if(auth()->check())
                <p style="color:#5C5C66; font-size:0.875rem; margin-top:0.25rem;">Hãy là người đầu tiên đăng bài!</p>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $posts->links() }}
    </div>
</div>
