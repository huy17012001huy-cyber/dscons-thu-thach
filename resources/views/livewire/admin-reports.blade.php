<div>
    <h1 style="font-size:1.25rem; font-weight:800; color:#1A1A1A; margin-bottom:1rem;">▲ Báo cáo từ thành viên</h1>

    @forelse($reports as $report)
    <div class="card mb-3">
        <div class="flex items-start gap-3">
            <img src="{{ $report->user->avatar_url }}" class="avatar w-8 h-8 shrink-0" alt="">
            <div style="flex:1; min-width:0;">
                <p style="font-size:0.8rem; color:#1A1A1A;">
                    <strong>{{ $report->user->name }}</strong> báo cáo
                    <span style="color:#5C5C66;">{{ class_basename($report->reportable_type) }} #{{ $report->reportable_id }}</span>
                </p>
                <p style="font-size:0.7rem; color:#5C5C66;">{{ $report->created_at->diffForHumans() }} · {{ $report->reason }}</p>

                @if($report->reportable)
                <div style="margin-top:0.5rem; padding:0.5rem; background:#F7F5F3; border-radius:0.375rem; border:1px solid #E1E1E1;">
                    <p style="font-size:0.75rem; color:#2E2E2E; line-height:1.4;">
                        {{ Str::limit($report->reportable->content ?? $report->reportable->body ?? '—', 200) }}
                    </p>
                    <p style="font-size:0.65rem; color:#5C5C66; margin-top:0.25rem;">
                        Bởi: {{ $report->reportable->user?->name ?? '—' }}
                    </p>
                </div>
                @else
                <p style="font-size:0.75rem; color:#991B1B; margin-top:0.5rem;">Nội dung đã bị xóa</p>
                @endif
            </div>
        </div>
        <div class="flex justify-end gap-2 mt-3">
            <button wire:click="dismiss({{ $report->id }})" class="btn btn-ghost" style="font-size:0.75rem; padding:0.3rem 0.625rem;">Bỏ qua</button>
            <button wire:click="reviewed({{ $report->id }})" class="btn btn-secondary" style="font-size:0.75rem; padding:0.3rem 0.625rem;">Đã xem</button>
            @if($report->reportable)
            <button wire:click="deleteReportable({{ $report->id }})" wire:confirm="Xóa nội dung vi phạm?" class="btn btn-danger" style="font-size:0.75rem; padding:0.3rem 0.625rem;">Xóa nội dung</button>
            @endif
        </div>
    </div>
    @empty
    <div class="card text-center py-8">
        <p style="color:#5C5C66;">Không có báo cáo nào đang chờ xử lý.</p>
    </div>
    @endforelse
</div>
