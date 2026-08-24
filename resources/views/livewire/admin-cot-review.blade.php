<div class="admin-cot-review-page">
<style>
    .admin-cot-review-page { max-width: 920px; margin: 0 auto; }
    .admin-cot-review-page .card { border-radius: 16px; border-color: #D7E5EA; }
    .admin-cot-review-page .card:hover { border-color: #A9CAD7; box-shadow: 0 7px 16px rgba(18,59,89,.07); }
</style>

    <h1 style="font-size:1.25rem; font-weight:800; color:#1A1A1A; margin-bottom:1rem;"><x-icon name="compass" size="22" /> Duyệt CỐT</h1>

    @forelse($pending as $post)
    <div class="card mb-3">
        <div class="flex items-start gap-3">
            <img src="{{ $post->user->avatar_url }}" class="avatar w-10 h-10 shrink-0" alt="">
            <div style="flex:1;">
                <div class="flex items-center gap-2 mb-1">
                    <span style="font-weight:600; font-size:0.85rem; color:#1A1A1A;">{{ $post->user->name }}</span>
                    <span class="badge badge-pillar-{{ $post->pillar }}" style="font-size:0.65rem;">{{ $post->pillar_label }}</span>
                    <span style="font-size:0.7rem; color:#5C5C66;">{{ $post->created_at->diffForHumans() }}</span>
                </div>
                @if($post->title)
                <h3 style="font-size:0.9rem; font-weight:700; color:#1A1A1A; margin-bottom:0.375rem;">{{ $post->title }}</h3>
                @endif
                <p style="font-size:0.8rem; color:#2E2E2E; line-height:1.6; margin-bottom:0.5rem;">{{ Str::limit($post->content, 300) }}</p>
                <p style="font-size:0.7rem; color:#d17856;">Đề cử bởi: {{ $post->cotBy?->name ?? '—' }}</p>
            </div>
        </div>
        <div class="flex justify-end gap-2 mt-3">
            <button wire:click="reject({{ $post->id }})" class="btn btn-danger" style="font-size:0.75rem; padding:0.3rem 0.625rem;">Từ chối</button>
            <button wire:click="approve({{ $post->id }})" class="btn btn-success" style="font-size:0.75rem; padding:0.3rem 0.625rem;">Duyệt CỐT</button>
        </div>
    </div>
    @empty
    <div class="card text-center py-8">
        <p style="color:#5C5C66;">Không có bài viết nào chờ duyệt CỐT.</p>
    </div>
    @endforelse
</div>
