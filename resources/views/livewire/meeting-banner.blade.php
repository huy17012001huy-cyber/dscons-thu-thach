<div>
    @if($meetings->count() > 0)
    @foreach($meetings as $meeting)
    @php
        $isLive = $meeting->meeting_at->isPast() && $meeting->meeting_at->copy()->addHours(2)->isFuture();
        $isUpcoming = $meeting->meeting_at->isFuture();
        $canJoin = in_array($meeting->id, $unlockedTaskIds);
        $timeLabel = $meeting->meeting_at->timezone('Asia/Ho_Chi_Minh')->format('H:i');
        $dateLabel = $meeting->meeting_at->timezone('Asia/Ho_Chi_Minh')->format('d/m');
    @endphp
    <div style="background:{{ $isLive ? '#DC2626' : '#1F77BE' }}; color:#FFF; padding:0.625rem 0; position:relative; z-index:40;">
        <div class="max-w-screen-xl mx-auto px-4 flex items-center justify-center gap-3 flex-wrap" style="min-height:2rem;">
            @if($isLive)
            <span style="background:#FFF; color:#DC2626; font-size:0.65rem; font-weight:800; padding:0.15rem 0.5rem; border-radius:999px; animation:pulse 1.5s infinite;">LIVE</span>
            @else
            <span style="background:rgba(255,255,255,0.2); font-size:0.65rem; font-weight:700; padding:0.15rem 0.5rem; border-radius:999px;">{{ $timeLabel }} · {{ $dateLabel }}</span>
            @endif

            <span style="font-size:0.85rem; font-weight:700;">
                {{ $isLive ? 'Meeting đang diễn ra!' : 'Meeting sắp tới!' }}
                {{ $meeting->expedition->title }} — Ngày {{ $meeting->day_number }}: {{ $meeting->title }}
            </span>

            @if($canJoin && $meeting->video_url)
            <a href="{{ $meeting->video_url }}" target="_blank" rel="noopener" style="background:#FFF; color:{{ $isLive ? '#DC2626' : '#1F77BE' }}; font-size:0.75rem; font-weight:700; padding:0.3rem 0.75rem; border-radius:999px; text-decoration:none; white-space:nowrap;">
                Tham gia Meeting →
            </a>
            @elseif(!$canJoin)
            <span style="font-size:0.7rem; opacity:0.8;">Làm đến nhiệm vụ ngày {{ $meeting->day_number }} để mở link — hoặc xin link trong group Telegram</span>
            @endif
        </div>
    </div>
    @endforeach
    @endif
</div>
