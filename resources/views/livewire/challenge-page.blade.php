<div>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 style="font-size:1.25rem; font-weight:800; color:#1A1A1A;">★ Challenge</h1>
            <p style="font-size:0.8rem; color:#5C5C66;">Thử thách thực chiến · Nâng cấp kỹ năng mỗi ngày</p>
        </div>
    </div>

    <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
        @forelse($challenges as $ch)
        <a href="{{ route('challenge.show', $ch->slug ?? $ch->id) }}" class="card block" style="text-decoration:none; transition:border-color 0.15s;">
            <div class="flex items-start justify-between mb-2">
                <h3 style="font-size:0.9rem; font-weight:700; color:#1A1A1A; flex:1; padding-right:0.5rem;">{{ $ch->title }}</h3>
                <span class="badge difficulty-{{ $ch->difficulty }}" style="font-size:0.65rem; white-space:nowrap;">{{ $ch->difficulty_label }}</span>
            </div>

            <div style="background:#F7F5F3; border-radius:0.5rem; padding:0.625rem; margin-bottom:0.75rem;">
                <p style="font-size:0.65rem; color:#5C5C66; margin-bottom:0.25rem;"> MỤC TIÊU</p>
                <p style="font-size:0.8rem; color:#d17856; font-weight:600;">{{ $ch->boss_name }}</p>
            </div>

            @if($ch->description)
            <p style="font-size:0.775rem; color:#5C5C66; margin-bottom:0.75rem; line-height:1.4;">{{ Str::limit($ch->description, 100) }}</p>
            @endif

            <div class="flex items-center gap-3 mb-3">
                <div class="text-center">
                    <p style="font-size:0.875rem; font-weight:700; color:#1A1A1A;">{{ $ch->required_days }}</p>
                    <p style="font-size:0.65rem; color:#5C5C66;">Ngày</p>
                </div>
                <div class="text-center">
                    @if($ch->price > 0)
                    <p style="font-size:0.8rem; font-weight:700; color:#d17856;">{{ number_format($ch->price, 0, ',', '.') }}đ</p>
                    <p style="font-size:0.65rem; color:#5C5C66;">Giá</p>
                    @else
                    <p style="font-size:0.8rem; font-weight:700; color:#059669;">Miễn phí</p>
                    <p style="font-size:0.65rem; color:#5C5C66;">Giá</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2">
                <img src="{{ $ch->leader->avatar_url }}" class="avatar w-6 h-6" alt="">
                <span style="font-size:0.75rem; color:#5C5C66;">Leader: <strong style="color:#1A1A1A;">{{ $ch->leader->name }}</strong></span>
            </div>
        </a>
        @empty
        <div class="card text-center py-12" style="grid-column:1/-1;">
            <p style="font-size:2rem; margin-bottom:0.5rem;">★</p>
            <p style="color:#5C5C66;">Không có challenge nào</p>
        </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $challenges->links() }}</div>
</div>
