<div class="feed-page">
    <style>
        .feed-page { width: min(100%, 860px); margin: 0 auto; }
        .feed-page-header { margin: .15rem 0 1rem; }
        .feed-page-header h1 { margin: 0; color: #102A3B; font-size: 1.05rem; font-weight: 800; }
        .feed-page-header p { margin: .2rem 0 0; color: #61798A; font-size: .78rem; }
        .feed-page .feed-toolbar { padding:5px; border:1px solid #C9DFEA; border-radius:14px; background:#FFFFFF; }
        .feed-page .feed-toolbar .tab-nav { gap:3px; }
        .feed-page .feed-toolbar .tab-item { min-height:40px; border-radius:10px; }
        .feed-page .feed-toolbar .tab-item:hover, .feed-page .feed-toolbar .tab-item.active { background:#DCECF7; color:#125A96; }
        .feed-page .feed-filter { min-height:40px; max-width:180px; border:1px solid #C9DFEA; border-radius:10px; background:#FFFFFF; color:#29485B; padding:.4rem .65rem; font-size:.78rem; font-weight:650; }
        .feed-page .feed-filter:focus-visible { outline:3px solid rgba(31,119,190,.25); outline-offset:2px; border-color:#1F77BE; }
        .feed-page .subject-chip-row { display:flex; flex-wrap:wrap; align-items:center; gap:.45rem; overflow:visible; padding:.15rem 0 .35rem; }
        .feed-page .subject-chip { display:inline-flex; align-items:center; gap:.35rem; min-height:38px; padding:.45rem .78rem; border:1px solid #C9DFEA; border-radius:999px; background:#FFFFFF; color:#29485B; font-size:.78rem; font-weight:700; white-space:nowrap; cursor:pointer; }
        .feed-page .subject-chip svg { color:inherit !important; }
        .feed-page .subject-chip:hover, .feed-page .subject-chip.active { border-color:#1F77BE; background:#1F77BE; color:#fff; }
        .feed-page .subject-chip-meo-hay { margin-left:auto; }
        .feed-loading-state { display:flex; flex-direction:column; gap:8px; min-height:214px; padding:14px; border:1px solid #D7E5EA; border-radius:16px; background:#fff; }
        .feed-loading-row { height:12px; border-radius:999px; background:#E1F4F7; }
        .feed-loading-row.short { width:42%; } .feed-loading-row.medium { width:68%; } .feed-loading-row.long { width:92%; }
        .feed-empty-cta { display:inline-flex; min-height:40px; align-items:center; margin-top:.55rem; padding:.55rem .8rem; border:1px solid #B8D7E6; border-radius:10px; color:#125A96; font-size:.78rem; font-weight:750; text-decoration:none; background:#EAF7FA; }
        @media (max-width:640px) { .feed-page .feed-toolbar { align-items:stretch; } .feed-page .feed-toolbar .tab-nav { overflow-x:auto; flex-wrap:nowrap; } .feed-page .feed-filter { max-width:145px; flex:1; } }
    </style>

    <header class="feed-page-header">
        <h1>Bảng tin</h1>
        <p>Nơi kỹ sư BIM/MEP hỏi đáp, chia sẻ workflow và kinh nghiệm dự án.</p>
    </header>

    @if($activeRune)
        <div class="rune-banner flex items-center justify-between mb-4">
            <div class="flex items-center gap-2"><span style="font-size:1.25rem;">~</span><div><p style="font-weight:700;color:#C2410C;font-size:.875rem;">Phù văn đang kích hoạt!</p><p style="color:#61798A;font-size:.75rem;">Comment đầu tiên vào bài của {{ $activeRune->user->name }} để nhận 2x EXP.</p></div></div>
            <a href="#post-{{ $activeRune->id }}" style="font-size:.75rem;color:#C2410C;font-weight:600;white-space:nowrap;">Đến bài →</a>
        </div>
    @endif

    @auth
        <livewire:compose-post />
    @endauth

    <div class="feed-toolbar flex items-center gap-1 mt-4 mb-3 flex-wrap">
        <div class="tab-nav" style="margin-bottom:0;border:0;flex:1;min-width:0;">
            <button wire:click="setTab('latest')" class="tab-item {{ $tab === 'latest' ? 'active' : '' }}" style="white-space:nowrap;">Mới nhất</button>
            <button wire:click="setTab('popular')" class="tab-item {{ $tab === 'popular' ? 'active' : '' }}" style="white-space:nowrap;">Phổ biến</button>
            <button wire:click="setTab('cot')" class="tab-item {{ $tab === 'cot' ? 'active' : '' }}" style="white-space:nowrap;">CỐT</button>
        </div>
        <select wire:model.live="post_type_id" wire:change="setPostType($event.target.value)" class="feed-filter" aria-label="Lọc theo loại nội dung">
            <option value="">Tất cả nội dung</option>
            @foreach($postTypes as $postType)<option value="{{ $postType->id }}">{{ $postType->name }}</option>@endforeach
        </select>
    </div>

    <div class="subject-chip-row" aria-label="Lọc theo chủ đề">
        <button type="button" wire:click="setSubject('')" class="subject-chip {{ !$subject_id ? 'active' : '' }}"><x-icon name="grid" size="15" />Tất cả</button>
        @foreach($subjects as $subject)
            @php($subjectIcon = match(mb_strtolower($subject->name)) {
                'thiết kế' => 'compass',
                'dựng hình' => 'cube',
                'bóc tách' => 'list',
                'phối hợp/combine' => 'layers',
                'family' => 'archive',
                'mẹo hay' => 'spark',
                default => 'tag',
            })
            <button type="button" wire:click="setSubject({{ $subject->id }})" class="subject-chip {{ $subject->slug === 'meo-hay' ? 'subject-chip-meo-hay' : '' }} {{ (int) $subject_id === $subject->id ? 'active' : '' }}"><x-icon name="{{ $subjectIcon }}" size="15" />{{ $subject->name }}</button>
        @endforeach
    </div>

    <div wire:loading.flex wire:target="setTab,setSubject,setPostType,gotoPage" class="feed-loading-state" role="status" aria-live="polite">
        <div class="feed-loading-row short"></div><div class="feed-loading-row medium"></div><div class="feed-loading-row long"></div><div class="feed-loading-row medium"></div><div class="feed-loading-row short"></div>
    </div>

    @foreach($pinnedPosts as $post)
        <div style="position:relative;margin-bottom:.75rem;"><div style="position:absolute;top:.75rem;right:.75rem;font-size:.65rem;color:#61798A;z-index:1;"><x-icon name="bookmark" size="12" /> Ghim</div><livewire:post-card :post="$post" :key="'pin-'.$post->id" /></div>
    @endforeach

    <div style="display:flex;flex-direction:column;gap:8px;">
        @forelse($posts as $post)
            <livewire:post-card :post="$post" :key="'p-'.$post->id" />
        @empty
            <div class="card empty-state"><span class="section-mark" aria-hidden="true">Feed</span><p style="color:#61798A;">Chưa có bài viết nào</p>@if(auth()->check())<p style="color:#61798A;font-size:.875rem;margin-top:.25rem;">Hãy là người đầu tiên đăng bài!</p><a href="#open-composer" class="feed-empty-cta">Viết bài đầu tiên</a>@endif</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $posts->links() }}</div>
</div>
