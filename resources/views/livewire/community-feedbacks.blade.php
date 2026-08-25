<div class="community-feedback-page" x-data>
    <style>
        .community-feedback-page { min-height:100%; margin:-1.5rem -2rem; padding:0 20px 38px; background:#F5F2EB; color:#111827; }
        .feedback-page-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; min-height:100px; max-width:1080px; margin:0 auto; }
        .feedback-page-header h1 { margin:0; color:#111827; font-size:1rem; font-weight:800; letter-spacing:-.015em; }
        .feedback-primary { display:inline-flex; align-items:center; justify-content:center; min-height:39px; padding:.55rem 1rem; border:0; border-radius:7px; background:#1F77BE; color:#fff; font-size:.82rem; font-weight:800; text-decoration:none; cursor:pointer; box-shadow:none; }
        .feedback-primary:hover { background:#125A96; }
        .feedback-empty { display:grid; justify-items:center; align-content:center; min-height:214px; max-width:1080px; margin:0 auto; padding:1.5rem; border-radius:17px; background:#fff; text-align:center; }
        .feedback-empty-mark { display:grid; place-items:center; width:49px; height:49px; margin-bottom:.65rem; border:3px solid #111827; border-radius:10px 10px 4px 4px; background:#1387D4; color:#fff; }
        .feedback-empty h2 { margin:0 0 .45rem; color:#111827; font-size:.94rem; font-weight:800; }
        .feedback-empty p { margin:0; color:#111827; font-size:.82rem; line-height:1.5; }
        .feedback-footnote { max-width:1080px; margin:31px auto 0; color:#47708F; font-size:.7rem; text-align:center; }
        .feedback-list { display:grid; gap:.75rem; max-width:1080px; margin:0 auto; }
        .feedback-ticket { padding:1rem 1.1rem; border:1px solid #E5E0D8; border-radius:12px; background:#fff; }
        .feedback-ticket-head { display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem; }
        .feedback-ticket-title { margin:0; color:#111827; font-size:.95rem; font-weight:800; }
        .feedback-meta { display:flex; align-items:center; gap:.45rem; flex-wrap:wrap; margin-top:.45rem; color:#61798A; font-size:.72rem; }
        .feedback-badge { display:inline-flex; align-items:center; min-height:23px; padding:.18rem .5rem; border-radius:999px; font-size:.69rem; font-weight:800; }
        .feedback-content { margin:.7rem 0 0; color:#273A4A; font-size:.84rem; line-height:1.6; white-space:pre-line; }
        .feedback-attachments { display:flex; flex-wrap:wrap; gap:.5rem; margin-top:.75rem; }
        .feedback-attachments img { width:76px; height:58px; border-radius:7px; border:1px solid #D7E5EA; object-fit:cover; }
        .feedback-modal-backdrop { position:fixed; inset:0; z-index:1001; display:grid; place-items:center; padding:1rem; background:rgba(15,31,43,.48); }
        .feedback-modal { width:min(100%,560px); max-height:calc(100vh - 2rem); overflow-y:auto; padding:1.25rem; border:1px solid #D7E5EA; border-radius:14px; background:#fff; box-shadow:0 24px 70px rgba(15,31,43,.24); }
        .feedback-modal-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1rem; }
        .feedback-modal-header h2 { margin:0; color:#123B59; font-size:1.05rem; }
        .feedback-close { display:grid; place-items:center; width:38px; height:38px; border:0; border-radius:8px; background:#F2F6F8; color:#61798A; cursor:pointer; }
        .feedback-form-label { display:block; margin-bottom:.38rem; color:#274C67; font-size:.78rem; font-weight:800; }
        .feedback-form-control { width:100%; border:1px solid #B9D3E1; border-radius:8px; padding:.62rem .7rem; color:#173B56; background:#fff; font:inherit; font-size:.84rem; outline:none; }
        .feedback-form-control:focus { border-color:#1F77BE; box-shadow:0 0 0 3px rgba(31,119,190,.14); }
        .feedback-type-grid { display:grid; grid-template-columns:1fr 1fr; gap:.55rem; }
        .feedback-type-option { display:flex; align-items:center; gap:.45rem; min-height:42px; padding:.5rem .65rem; border:1px solid #B9D3E1; border-radius:8px; color:#47657A; cursor:pointer; font-size:.78rem; font-weight:750; }
        .feedback-type-option.is-selected { border-color:#1F77BE; background:#EAF4FC; color:#125A96; }
        .feedback-upload { display:flex; align-items:center; justify-content:center; min-height:82px; padding:.7rem; border:1px dashed #9DC5D9; border-radius:9px; background:#F7FBFE; text-align:center; cursor:pointer; }
        .feedback-upload-preview { display:flex; flex-wrap:wrap; gap:.45rem; margin-top:.55rem; }
        .feedback-upload-item { position:relative; }
        .feedback-upload-item img { width:76px; height:56px; border:1px solid #D7E5EA; border-radius:6px; object-fit:cover; }
        .feedback-remove { position:absolute; top:-7px; right:-7px; display:grid; place-items:center; width:21px; height:21px; border:1px solid #fff; border-radius:50%; background:#125A96; color:#fff; cursor:pointer; }
        .feedback-modal-actions { display:flex; justify-content:flex-end; gap:.55rem; margin-top:1rem; }
        @media (max-width:640px) { .community-feedback-page { margin:-.75rem -.65rem; padding:0 12px 30px; } .feedback-page-header { min-height:84px; } .feedback-empty { min-height:214px; } .feedback-type-grid { grid-template-columns:1fr; } .feedback-modal-actions { flex-direction:column-reverse; } .feedback-modal-actions a,.feedback-modal-actions button { width:100%; justify-content:center; } }
    </style>

    <div class="feedback-page-header">
        <h1>Góp ý &amp; Khiếu nại</h1>
        <a href="{{ community_route('feedbacks.create') }}" class="feedback-primary">Gửi góp ý</a>
    </div>

    @if($feedbacks->isEmpty())
        <div class="feedback-empty">
            <div class="feedback-empty-mark" aria-hidden="true"><x-icon name="mail" size="25" color="#fff" /></div>
            <h2>Bạn chưa gửi phiếu nào</h2>
            <p>Có điều gì muốn nói với ban quản trị? Gửi một phiếu, chỉ ban quản trị đọc được.</p>
        </div>
        <p class="feedback-footnote">Website đang trong quá trình xin giấy phép.</p>
    @else
        <div class="feedback-list">
            @foreach($feedbacks as $feedback)
                @php
                    $statusLabel = ['pending' => 'Chờ xử lý', 'reviewed' => 'Đã xem', 'resolved' => 'Đã giải quyết'][$feedback->status] ?? $feedback->status;
                    $statusStyle = ['pending' => 'background:#FFF7E6;color:#9A6700;', 'reviewed' => 'background:#EAF4FC;color:#125A96;', 'resolved' => 'background:#ECF9F1;color:#166534;'][$feedback->status] ?? 'background:#F1F5F9;color:#475569;';
                @endphp
                <article class="feedback-ticket">
                    <div class="feedback-ticket-head">
                        <div>
                            <h2 class="feedback-ticket-title">{{ $feedback->subject }}</h2>
                            <div class="feedback-meta">
                                <span class="feedback-badge" style="{{ match($feedback->type) {
                                    'khieu_nai' => 'background:#FFF1F2;color:#BE123C;',
                                    'bao_loi' => 'background:#FFF7E6;color:#9A6700;',
                                    'thanh_toan' => 'background:#EAF4FC;color:#125A96;',
                                    'khac' => 'background:#F1F5F9;color:#475569;',
                                    default => 'background:#EAF4FC;color:#125A96;',
                                } }}">{{ ['khieu_nai' => 'Khiếu nại', 'bao_loi' => 'Báo lỗi', 'thanh_toan' => 'Thanh toán', 'khac' => 'Khác', 'gop_y' => 'Góp ý'][$feedback->type] ?? $feedback->type }}</span>
                                <span class="feedback-badge" style="{{ $statusStyle }}">{{ $statusLabel }}</span>
                                @if($isAdmin && $feedback->user)<span>{{ $feedback->user->name }}</span><span aria-hidden="true">·</span>@endif
                                <time datetime="{{ $feedback->created_at->toIso8601String() }}">{{ $feedback->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</time>
                            </div>
                        </div>
                    </div>
                    <p class="feedback-content">{{ $feedback->content }}</p>
                    @if($feedback->attachments)
                        <div class="feedback-attachments" aria-label="Ảnh đính kèm">
                            @foreach($feedback->attachments as $attachment)
                                <a href="{{ asset('storage/'.$attachment) }}" target="_blank" rel="noopener" aria-label="Mở ảnh đính kèm"><img src="{{ asset('storage/'.$attachment) }}" alt="Ảnh đính kèm"></a>
                            @endforeach
                        </div>
                    @endif
                    @if($feedback->admin_notes)
                        <div style="margin-top:.75rem;padding:.6rem .7rem;border-left:3px solid #1F77BE;background:#F3F8FC;color:#34495E;font-size:.78rem;line-height:1.5;"><strong style="color:#125A96;">Phản hồi từ đội ngũ:</strong><br>{{ $feedback->admin_notes }}</div>
                    @endif
                </article>
            @endforeach
        </div>
    @endif

</div>
