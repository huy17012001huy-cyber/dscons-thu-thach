<div class="challenge-detail-page">
<style>
    .challenge-curriculum { margin: 0 0 1rem; padding: 1rem; border: 1px solid #B8D7E6; border-radius: 12px; background: #F7FBFE; color: #123B59; }
    .challenge-curriculum-head { display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem; margin-bottom:.65rem; }
    .challenge-curriculum-head h3 { margin:.15rem 0 0; color:#125A96; font-size:1rem; font-weight:800; }
    .challenge-eyebrow { color:#61798A; font-size:.65rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
    .challenge-track { flex:0 0 auto; padding:.22rem .5rem; border-radius:999px; background:#E1F4F7; color:#125A96; font-size:.65rem; font-weight:800; }
    .challenge-curriculum-why { margin:.5rem 0 .8rem; color:#456477; font-size:.78rem; line-height:1.55; }
    .challenge-curriculum-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; }
    .challenge-curriculum h4 { margin:.55rem 0 .3rem; color:#125A96; font-size:.75rem; font-weight:800; }
    .challenge-curriculum p, .challenge-curriculum li { color:#456477; font-size:.76rem; line-height:1.55; }
    .challenge-curriculum ul, .challenge-curriculum ol { margin:.3rem 0 0; padding-left:1.15rem; }
    .challenge-curriculum-details { margin-top:.8rem; padding-top:.65rem; border-top:1px solid #D7E5EE; }
    .challenge-curriculum-details summary { color:#125A96; cursor:pointer; font-size:.76rem; font-weight:800; }
    .challenge-prompt-box { margin-top:.8rem; overflow:hidden; border:1px solid #D7E5EE; border-radius:8px; background:#123B59; }
    .challenge-prompt-title { display:flex; align-items:center; justify-content:space-between; gap:.5rem; padding:.45rem .65rem; color:#fff; font-size:.72rem; }
    .challenge-prompt-title button { min-height:30px; padding:.25rem .55rem; border:1px solid rgba(255,255,255,.45); border-radius:6px; color:#fff; background:transparent; cursor:pointer; font-size:.68rem; }
    .challenge-prompt-box pre { max-height:260px; overflow:auto; margin:0; padding:.7rem; color:#DDF5FF; font: .68rem/1.55 ui-monospace,SFMono-Regular,Consolas,monospace; white-space:pre-wrap; }
    .challenge-submission-guide { display:flex; flex-direction:column; gap:.45rem; margin-top:.8rem; padding:.75rem; border-left:3px solid #1F77BE; background:#EAF5FB; }
    .challenge-submission-guide h4 { margin:.1rem 0 .2rem; }
    .challenge-submission-guide label { display:flex; flex-direction:column; gap:.25rem; color:#456477; font-size:.73rem; line-height:1.4; }
    .challenge-submission-guide label:has(input[type="checkbox"]) { flex-direction:row; align-items:flex-start; }
    .challenge-submission-guide input[type="checkbox"] { accent-color:#1F77BE; margin-top:.18rem; }
    .challenge-submission-guide textarea { width:100%; min-height:55px; font-size:.74rem; }
    .challenge-evidence-hint { margin:.15rem 0 0; color:#61798A; font-size:.72rem; line-height:1.5; }
    .challenge-share-note { margin:.7rem 0 0; padding:.55rem .7rem; border-left:3px solid #F39402; background:#FFF8E9; color:#765D31; font-size:.72rem; line-height:1.5; }
    .challenge-share-note strong { color:#9A5B00; }
    .challenge-rubric-preview { display:flex; flex-wrap:wrap; align-items:center; gap:.35rem .5rem; margin-top:.8rem; padding-top:.65rem; border-top:1px solid #D7E5EE; color:#456477; font-size:.68rem; }
    .challenge-rubric-preview strong { color:#125A96; }
    .challenge-rubric-preview span { padding:.2rem .4rem; border:1px solid #B8D7E6; border-radius:5px; background:#fff; }
    .challenge-rubric-preview small { flex-basis:100%; color:#61798A; line-height:1.45; }
    @media (max-width: 640px) { .challenge-curriculum-grid { grid-template-columns:1fr; gap:.25rem; } .challenge-curriculum { padding:.75rem; } }

    .challenge-detail-page { width: min(100%, 960px); margin: 0 auto; }
    .challenge-detail-page > .card { border-radius: 18px; border-color: #D7E5EA; box-shadow: 0 2px 10px rgba(18,59,89,.045); }
    .challenge-detail-page > .card + .card { margin-top: 16px; }
    .challenge-detail-page .card .border-b { border-color: #E4EDF1 !important; }
    .challenge-detail-page .card h1 { text-wrap: balance; }
    .challenge-detail-page .card a:not(.btn) { text-underline-offset: 3px; }
    .challenge-detail-page .card input:focus, .challenge-detail-page .card textarea:focus, .challenge-detail-page .card select:focus { border-color: #1F77BE; box-shadow: 0 0 0 3px rgba(31,119,190,.14); }
    .challenge-detail-page [style*="#1A1A1A"] { color:#102A3B !important; }
    .challenge-detail-page [style*="#2E2E2E"] { color:#29485B !important; }
    .challenge-detail-page [style*="#5C5C66"] { color:#61798A !important; }
    .challenge-detail-page [style*="#d17856"] { color:#F39402 !important; }
    .challenge-detail-page [style*="#059669"] { color:#147F96 !important; }
    .challenge-detail-page [style*="#F7F5F3"] { background:#F1F6F8 !important; }
    .challenge-detail-page [style*="#D1FAE5"] { background:#E6F6F8 !important; color:#147F96 !important; }
    .challenge-detail-page [style*="#FEF3C7"] { background:#FFF8E9 !important; border-color:#F5D58A !important; }
    @media (max-width: 640px) {
        .challenge-detail-page > .card { padding: 16px !important; border-radius: 15px; }
        .challenge-detail-page .flex.items-center.justify-between { gap: 10px; }
    }
</style>

    <div class="mb-4">
        <a href="{{ route('challenge') }}" style="font-size:0.8rem; color:#5C5C66;">← Quay lại Challenge</a>
    </div>

    {{-- Header --}}
    <div class="card mb-4">
        <div class="flex items-start justify-between mb-3">
            <div>
                <h1 style="font-size:1.25rem; font-weight:800; color:#1A1A1A;">{{ $expedition->title }}</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="badge difficulty-{{ $expedition->difficulty }}">{{ $expedition->difficulty_label }}</span>
                    @if($expedition->price > 0)
                    <span class="badge" style="background:#FEF3C7; color:#92400E; border:1px solid #FDE68A;">{{ number_format($expedition->price, 0, ',', '.') }}đ</span>
                    @else
                    <span class="badge" style="background:#D1FAE5; color:#065F46;">Miễn phí</span>
                    @endif
                    @if(($expedition->access_tier ?? 'premium') === 'premium')
                    <span class="badge" style="background:#E1F4F7;color:#125A96;border:1px solid #B8D7E6;">Premium</span>
                    @endif
                </div>
            </div>
        </div>

        <div style="background:#F7F5F3; border-radius:0.5rem; padding:0.75rem; margin-bottom:1rem;">
            <p style="font-size:0.7rem; color:#5C5C66; margin-bottom:0.25rem;"> MỤC TIÊU</p>
            <p style="font-size:0.9rem; color:#d17856; font-weight:700;">{{ $expedition->boss_name }}</p>
        </div>

        @if($expedition->description)
        <p style="font-size:0.85rem; color:#2E2E2E; margin-bottom:1rem; line-height:1.5; white-space:pre-line;">{!! preg_replace('~(https?://(?:(?!&quot;|&lt;|&gt;)\S)+)~', '<a href="$1" target="_blank" rel="noopener noreferrer" style="color:#d17856; text-decoration:underline;">$1</a>', e($expedition->description)) !!}</p>
        @endif

        <div class="flex flex-wrap gap-4 mb-4">
            <div>
                <p style="font-size:0.7rem; color:#5C5C66;">Leader</p>
                <div class="flex items-center gap-2 mt-1">
                    <img src="{{ $expedition->leader->avatar_url }}" class="avatar w-6 h-6" alt="">
                    <a href="{{ route('profile', $expedition->leader->username ?? $expedition->leader->id) }}" style="font-size:0.8rem; font-weight:600; color:#1A1A1A;">{{ $expedition->leader->name }}</a>
                </div>
            </div>
            <div>
                <p style="font-size:0.7rem; color:#5C5C66;">Thành viên</p>
                <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A; margin-top:0.25rem;">{{ $approvedMembers->count() }}</p>
            </div>
            <div>
                <p style="font-size:0.7rem; color:#5C5C66;">Thời gian</p>
                <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A; margin-top:0.25rem;">{{ $expedition->required_days }} ngày</p>
            </div>
        </div>

        {{-- Freeze banner moved to site-wide (resources/views/layouts/app.blade.php) --}}

        {{-- Admin preview banner --}}
        @if($isAdminPreview ?? false)
        <div style="background:#FEF3C7; border:1px solid #FDE68A; border-radius:0.5rem; padding:0.625rem 0.875rem; margin-bottom:0.75rem; display:flex; align-items:center; justify-content:space-between; gap:0.5rem; flex-wrap:wrap;">
            <span style="font-size:0.78rem; color:#92400E;"><strong>Xem trước quản trị</strong> — đang xem như thành viên (không lưu DB, không nhận XP). Mọi nhiệm vụ đều được mở để kiểm tra UX.</span>
            <span style="font-size:0.7rem; color:#92400E; opacity:0.7;">Dùng nút Sửa trên nhiệm vụ để chỉnh sửa</span>
        </div>
        @endif

        {{-- Join / Status buttons --}}
        <div class="flex flex-wrap gap-2">
            @auth
            @if($isAdminPreview ?? false)
            {{-- Admin preview: skip join button, no member status --}}
            @elseif(($premiumLocked ?? false))
            <div style="padding:.75rem 1rem;border:1px solid #B8D7E6;background:#F7FCFD;border-radius:.6rem;display:flex;align-items:center;gap:10px;">
                <span style="width:28px;height:28px;display:grid;place-items:center;border-radius:8px;background:#E1F4F7;color:#1F77BE;">★</span>
                <span style="font-size:.8rem;color:#456477;">Challenge Premium — nâng hạng membership để đăng ký và mở nhiệm vụ.</span>
                <a href="{{ community_route('membership') }}" class="btn btn-primary" style="margin-left:auto;text-decoration:none;white-space:nowrap;">Nâng hạng</a>
            </div>
            @elseif(!$isApproved && !$isPending && !$isPendingPayment)
            <button wire:click="requestJoin" class="btn btn-primary">
                {{ $expedition->price > 0 ? 'Đăng ký tham gia · ' . number_format($expedition->price, 0, ',', '.') . 'đ' : 'Đăng ký tham gia' }}
            </button>
            @elseif($isPendingPayment)
            {{-- Chờ chuyển khoản: QR VietQR + poll, webhook SePay tự duyệt khi tiền vào --}}
            @php
                $transferCode = 'CHAL' . $expedition->id . 'U' . auth()->id();
                $bankAccount = config('services.sepay.bank_account');
                $bankName = config('services.sepay.bank_name');
                $qrUrl = $bankAccount
                    ? 'https://qr.sepay.vn/img?' . http_build_query(['acc' => $bankAccount, 'bank' => $bankName, 'amount' => (int) $expedition->price, 'des' => $transferCode, 'template' => 'compact'])
                    : null;
            @endphp
            <div wire:poll.5s style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:0.5rem; padding:0.75rem; width:100%; max-width:340px;">
                <p style="font-size:0.8rem; font-weight:600; color:#92400E; margin-bottom:0.375rem;">⏳ Chờ thanh toán</p>

                @if($qrUrl)
                <div class="text-center" style="margin-bottom:0.75rem;">
                    <img src="{{ $qrUrl }}" alt="QR thanh toán" style="max-width:220px; margin:0 auto; border-radius:0.5rem;">
                    <p style="font-size:0.7rem; color:#5C5C66; margin-top:0.375rem;">Quét mã QR bằng app ngân hàng</p>
                </div>
                @else
                <p style="font-size:0.75rem; color:#991B1B; margin-bottom:0.5rem;">Chưa cấu hình tài khoản nhận tiền — liên hệ admin (Cài đặt → SePay).</p>
                @endif

                <div style="background:#FFFFFF; border:1px solid #E1E1E1; border-radius:0.375rem; padding:0.625rem; margin-bottom:0.375rem;">
                    <div class="flex justify-between items-center" style="margin-bottom:0.25rem;">
                        <span style="font-size:0.75rem; color:#5C5C66;">Nội dung CK:</span>
                        <strong style="font-size:0.85rem; color:#d17856; letter-spacing:0.05em;">{{ $transferCode }}</strong>
                    </div>
                    <div class="flex justify-between items-center">
                        <span style="font-size:0.75rem; color:#5C5C66;">Số tiền:</span>
                        <strong style="font-size:0.85rem; color:#1A1A1A;">{{ number_format($expedition->price, 0, ',', '.') }}đ</strong>
                    </div>
                </div>

                <p style="font-size:0.7rem; color:#059669; font-weight:500;">⚡ Trang tự cập nhật khi nhận được tiền (1-3 phút)</p>
                <button wire:click="cancelRequest" wire:confirm="Huỷ đăng ký thử thách này?" style="font-size:0.72rem; color:#991B1B; cursor:pointer; margin-top:0.5rem; font-weight:500;">Huỷ đăng ký</button>
            </div>
            @elseif($isPending)
            <div class="flex items-center gap-2 px-4 py-2 rounded-lg" style="background:#FEF3C7; border:1px solid #FDE68A;">
                <span style="font-size:0.85rem;">⏳</span>
                <span style="font-size:0.8rem; font-weight:600; color:#92400E;">Đang chờ Admin duyệt</span>
                <button wire:click="cancelRequest" wire:confirm="Rút yêu cầu tham gia?" style="font-size:0.75rem; color:#991B1B; cursor:pointer; margin-left:0.5rem; font-weight:500;">Rút lại</button>
            </div>
            @elseif($isApproved && $personalDaysLeft === null)
            {{-- Approved but not started yet --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-4 py-2 rounded-lg" style="background:#D1FAE5; border:1px solid #A7F3D0;">
                    <span style="font-size:0.85rem;">✓</span>
                    <span style="font-size:0.8rem; font-weight:600; color:#065F46;">Đã được duyệt</span>
                </div>
                <button wire:click="startMyChallenge" wire:confirm="Bắt đầu thử thách? Nhiệm vụ sẽ unlock hàng ngày từ bây giờ." class="btn btn-primary" style="font-size:0.85rem; padding:0.5rem 1.25rem;">
                    Bắt đầu thử thách
                </button>
            </div>
            @elseif($isApproved && $personalDaysLeft !== null)
            <div class="flex items-center gap-2 px-4 py-2 rounded-lg" style="background:#D1FAE5; border:1px solid #A7F3D0;">
                <span style="font-size:0.85rem;">✓</span>
                <span style="font-size:0.8rem; font-weight:600; color:#065F46;">Đang tham gia · Ngày {{ $currentDay }}/{{ $expedition->required_days }} · Còn {{ $personalDaysLeft }} ngày</span>
            </div>


            {{-- Video Feedback inline --}}
            @if($myMember && !($isAdminPreview ?? false))
            @php $vfStatus = $myMember->video_feedback_status; @endphp
            <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:0.5rem; padding:0.75rem; margin-top:0.75rem;">
                @if($vfStatus === 'approved')
                <p style="font-size:0.8rem; font-weight:600; color:#065F46;">✓ Video Feedback đã duyệt — ban tổ chức sẽ liên hệ bạn về phần thưởng!</p>
                @elseif($vfStatus === 'pending')
                <p style="font-size:0.8rem; font-weight:600; color:#92400E;">▶ Video Feedback đang chờ duyệt...</p>
                @else
                <p style="font-size:0.8rem; font-weight:700; color:#92400E; margin-bottom:0.375rem;">▶ Gửi Video Feedback</p>
                <p style="font-size:0.75rem; color:#5C5C66; margin-bottom:0.5rem;">Quay video cảm nhận chân thật về hành trình của bạn. Video được duyệt sẽ nhận phần thưởng từ ban tổ chức.</p>
                @if($vfStatus === 'rejected')
                <p style="font-size:0.75rem; color:#DC2626; margin-bottom:0.375rem;">✗ {{ $myMember->video_feedback_note }}</p>
                @endif
                <div class="flex gap-2">
                    <input wire:model="videoFeedbackUrl" type="url" class="input" placeholder="Paste link video..." style="font-size:0.8rem; flex:1;">
                    <button wire:click="submitVideoFeedback" wire:loading.attr="disabled" wire:loading.class="opacity-50" wire:target="submitVideoFeedback" class="btn btn-primary" style="font-size:0.8rem; white-space:nowrap;">Gửi</button>
                </div>
                @error('videoFeedbackUrl') <p style="font-size:0.7rem; color:#991B1B; margin-top:0.25rem;">{{ $message }}</p> @enderror
                @endif
            </div>
            @endif

            @endif
            @else
            <a href="{{ route('login') }}" class="btn btn-primary">Đăng nhập để tham gia</a>
            @endauth
        </div>

        {{-- Giá tham gia (ẩn với member tạo qua webhook — đã mua qua funnel, được duyệt tự động) --}}
        @if($expedition->price > 0 && !$isApproved && !$isPending && !$isPendingPayment && !($isWebhookMember ?? false))
        <div style="margin-top:1rem; padding:1rem; background:#F0FDF4; border:1px solid #A5D6A7; border-radius:0.75rem;">
            <p style="font-size:0.85rem; font-weight:700; color:#1B5E20; margin-bottom:0.5rem;">▣ Phí tham gia</p>
            <p style="font-size:1.1rem; font-weight:800; color:#d17856;">{{ number_format($expedition->price, 0, ',', '.') }}đ</p>
            <p style="font-size:0.75rem; color:#5C5C66; margin-top:0.375rem;">Bấm "Đăng ký tham gia" để nhận mã QR chuyển khoản. Hệ thống tự duyệt ngay khi nhận được tiền.</p>
        </div>
        @endif
    </div>

    <a href="{{ route('events', ['challengeFilter' => $expedition->id]) }}" class="event-related" style="display:inline-flex;align-items:center;gap:.35rem;margin:-.5rem 0 1rem;color:#1F77BE;font-size:.78rem;font-weight:700;text-decoration:none;">
        Xem sự kiện liên quan →
    </a>

    {{-- Admin: Pending requests --}}
    @if($pendingMembers->count() > 0)
    <div class="card mb-4" style="border-left:3px solid #F59E0B;">
        <h2 style="font-size:0.9rem; font-weight:700; color:#92400E; margin-bottom:0.75rem;">⏳ Yêu cầu tham gia ({{ $pendingMembers->count() }})</h2>
        <div class="flex flex-col gap-2">
            @foreach($pendingMembers as $pm)
            <div class="flex items-center gap-3 py-2 {{ !$loop->last ? 'border-b' : '' }}" style="{{ !$loop->last ? 'border-color:#E1E1E1;' : '' }}">
                <img src="{{ $pm->user->avatar_url }}" class="avatar w-8 h-8" alt="">
                <div style="flex:1;">
                    <a href="{{ route('profile', $pm->user->username ?? $pm->user->id) }}" style="font-size:0.8rem; font-weight:600; color:#1A1A1A;">{{ $pm->user->name }}</a>
                    <p style="font-size:0.65rem; color:#5C5C66;">{{ $pm->joined_at->diffForHumans() }}</p>
                </div>
                <div class="flex gap-1">
                    <button wire:click="approveRequest({{ $pm->id }})"
                            wire:confirm="Duyệt cho {{ $pm->user->name }} tham gia thử thách?"
                            class="btn btn-primary" style="font-size:0.7rem; padding:0.25rem 0.625rem;">Duyệt</button>
                    <button wire:click="rejectRequest({{ $pm->id }})"
                            wire:confirm="Từ chối yêu cầu của {{ $pm->user->name }}?"
                            class="btn btn-ghost" style="font-size:0.7rem; padding:0.25rem 0.625rem; color:#991B1B;">Từ chối</button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Daily Tasks --}}
    @if($tasks->count() > 0 && $isApproved)
    <div class="card mb-4">
        <div class="flex items-center justify-between mb-3">
            <h2 style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">Nhiệm vụ hàng ngày</h2>
            <span style="font-size:0.75rem; color:#5C5C66;">{{ $completedTaskCount }}/{{ $tasks->count() }} hoàn thành</span>
        </div>

        <div style="height:6px; background:#EEECE9; border-radius:3px; margin-bottom:1rem; overflow:hidden;">
            <div style="height:100%; background:#d17856; border-radius:3px; width:{{ $tasks->count() > 0 ? round($completedTaskCount / $tasks->count() * 100) : 0 }}%; transition:width 0.3s;"></div>
        </div>

        <div class="flex flex-col gap-3">
            @foreach($tasks as $task)
            @php
                $isAdminUser = auth()->check() && auth()->user()->is_admin;
                $isFrozen = $expedition->freeze_from_day
                    && $task->day_number == $expedition->freeze_from_day
                    && $expedition->isFreezeActive();
                $isCompleted = in_array($task->id, $completedTaskIds);
                $isUnlocked = ($task->day_number <= $currentDay && (!$isFrozen || $isAdminUser)) || $isCompleted;
                $isRejected = in_array($task->id, $rejectedTaskIds ?? []);
                $statusPending = in_array($task->id, $pendingTaskIds ?? []);
                $statusApproved = $isCompleted && !$isRejected && !$statusPending;
                $isLate = in_array($task->id, $lateTaskIds ?? []);
                $deadline = ($isAdminPreview ?? false) ? null : ($contestEnds[$task->id] ?? $taskDeadlines[$task->id] ?? null);
                $isOverdue = $isUnlocked && !$isFrozen && $deadline && now()->greaterThan($deadline) && !$isCompleted;
            @endphp
            <div x-data="{ expanded: false }"
                 class="py-3 {{ !$loop->last ? 'border-b' : '' }}" style="{{ !$loop->last ? 'border-color:#E1E1E1;' : '' }} position:relative;">

                {{-- Admin edit button (outside opacity wrapper so it stays visible on locked tasks) --}}
                @can('community-admin')
                <button wire:click="startEditTask({{ $task->id }})" @click.stop style="position:absolute; top:0.75rem; right:1.5rem; color:#5C5C66; font-size:0.65rem; padding:0.125rem 0.375rem; cursor:pointer; z-index:1;" title="Sửa nhiệm vụ" aria-label="Sửa nhiệm vụ">Sửa</button>
                @endcan

                <div style="{{ !$isUnlocked ? 'opacity:0.5;' : '' }}">
                {{-- Task header --}}
                <div class="flex items-start gap-3"
                     @if($isUnlocked) role="button" tabindex="0" :aria-expanded="expanded.toString()" @keydown.enter.prevent="expanded = !expanded" @keydown.space.prevent="expanded = !expanded" @endif
                     @click="if({{ $isUnlocked ? 'true' : 'false' }}) expanded = !expanded"
                     style="{{ $isUnlocked ? 'cursor:pointer;' : '' }}">
                    @if($isRejected)
                    <span style="font-size:1rem; margin-top:0.1rem; color:#DC2626;">✗</span>
                    @elseif($statusPending)
                    <span style="font-size:1rem; margin-top:0.1rem; color:#92400E;">⧗</span>
                    @elseif($statusApproved && $isLate)
                    <span style="font-size:1rem; margin-top:0.1rem;">!</span>
                    @elseif($statusApproved)
                    <span style="font-size:1rem; margin-top:0.1rem;">✓</span>
                    @elseif($isFrozen)
                    <span style="font-size:1rem; margin-top:0.1rem;">⏸</span>
                    @elseif($isOverdue)
                    <span style="font-size:1rem; margin-top:0.1rem;">!</span>
                    @elseif(!$isUnlocked)
                    <span style="font-size:1rem; margin-top:0.1rem;">▪</span>
                    @else
                    <span style="font-size:1rem; margin-top:0.1rem;">▢</span>
                    @endif
                    <div style="flex:1;">
                        <div class="flex items-center gap-2" style="flex-wrap:wrap;">
                            <span style="font-size:0.7rem; font-weight:600; color:#d17856; background:#E8F5E9; padding:0.125rem 0.5rem; border-radius:999px;">Ngày {{ $task->day_number }}</span>
                            @if($task->is_contest && $statusApproved)
                            <span style="font-size:0.65rem; font-weight:700; color:#78350F; background:#FFF1D6; padding:0.15rem 0.55rem; border-radius:999px; border:1px solid #F59E0B; box-shadow:0 1px 2px rgba(245,158,11,0.2);">
                                Mini-game
                            </span>
                            @endif
                            @if($isRejected)
                            <span style="font-size:0.65rem; color:#DC2626; font-weight:500;">Cần nộp lại</span>
                            @elseif($statusPending)
                            <span style="font-size:0.65rem; color:#92400E; font-weight:500;">Chờ duyệt</span>
                            @elseif($statusApproved && $isLate)
                            <span style="font-size:0.65rem; color:#D97706; font-weight:500;">Hoàn thành (Trễ)</span>
                            @elseif($statusApproved)
                            <span style="font-size:0.65rem; color:#059669; font-weight:500;">Hoàn thành</span>
                            @elseif($isFrozen)
                            <span style="font-size:0.65rem; color:#92400E; font-weight:500;">⏸ Tạm dừng · Tiếp tục {{ $expedition->freeze_ends_at->timezone('Asia/Ho_Chi_Minh')->format('d/m') }}</span>
                            @elseif($isOverdue)
                            <span style="font-size:0.65rem; color:#DC2626; font-weight:500;">Quá hạn</span>
                            @elseif($isUnlocked && $deadline)
                            <span style="font-size:0.65rem; color:#d17856; font-weight:500;">Đang mở · Hạn {{ $deadline->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m') }}</span>
                            @elseif($isUnlocked)
                            <span style="font-size:0.65rem; color:#d17856; font-weight:500;">Đang mở</span>
                            @elseif($task->day_number === $currentDay + 1 && $nextDayUnlockAt)
                            <span style="font-size:0.65rem; color:#5C5C66; font-weight:500;">Mở lúc {{ $nextDayUnlockAt->timezone('Asia/Ho_Chi_Minh')->format('H:i · d/m') }}</span>
                            @endif
                            @if($task->meeting_at)
                            <span style="font-size:0.6rem; color:#1F77BE; background:#E1F4F7; padding:0.1rem 0.375rem; border-radius:4px; font-weight:500;">▶ Meeting · {{ $task->meeting_at->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m') }}</span>
                            @elseif($task->video_url && preg_match('/meet\.google|zoom\.us/i', $task->video_url))
                            <span style="font-size:0.6rem; color:#1F77BE; background:#E1F4F7; padding:0.1rem 0.375rem; border-radius:4px; font-weight:500;">▶ Meeting</span>
                            @endif
                            @if($task->reward_file_path && $statusApproved)
                            <a href="{{ route('challenge.reward.download', ['slug' => $expedition->slug, 'day' => $task->day_number]) }}" @click.stop style="font-size:0.65rem; color:#1E40AF; background:#DBEAFE; padding:0.15rem 0.5rem; border-radius:999px; font-weight:600; border:1px solid #BFDBFE; text-decoration:none; display:inline-flex; align-items:center; gap:0.25rem;">⬇ Tài liệu</a>
                            @endif
                        </div>
                        @if($isUnlocked || $isCompleted)
                        <p style="font-size:0.825rem; font-weight:600; color:#1A1A1A; margin-top:0.25rem;">{{ $task->title }}</p>
                        @elseif($isFrozen)
                        <p style="font-size:0.825rem; font-weight:500; color:#5C5C66; margin-top:0.25rem; font-style:italic;">Nhiệm vụ tạm dừng, mở lại {{ $expedition->freeze_ends_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y') }}</p>
                        @else
                        <p style="font-size:0.825rem; font-weight:500; color:#5C5C66; margin-top:0.25rem; font-style:italic;">Nhiệm vụ này vẫn còn là một bí ẩn, sẽ được unlock sau</p>
                        @endif
                    </div>
                    @if($isUnlocked)
                    <svg x-bind:style="expanded ? 'transform:rotate(180deg)' : ''" class="w-4 h-4 transition-transform" style="color:#5C5C66; margin-top:0.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    @endif

                </div>
                </div>{{-- /opacity wrapper --}}

                {{-- Admin edit form --}}
                @if($editingTaskId === $task->id)
                <div style="margin-top:0.75rem; padding:0.75rem; background:#F0FDF4; border-radius:0.5rem;" @click.stop>
                    <label style="display:block; font-size:0.75rem; font-weight:600; color:#1B5E20; margin-bottom:0.25rem;">Tên nhiệm vụ</label>
                    <input wire:model="editTaskTitle" class="input" placeholder="VD: Tham gia Meeting Kick Off" style="font-size:0.8rem; margin-bottom:0.5rem;">
                    <label style="display:block; font-size:0.75rem; font-weight:600; color:#1B5E20; margin-bottom:0.25rem;">Mô tả ngắn</label>
                    <textarea wire:model="editTaskDesc" class="input" rows="2" placeholder="Mô tả nhiệm vụ..."
                        x-data x-init="$el.style.height = $el.scrollHeight + 'px'"
                        @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                        @paste="window.pasteAsMarkdown($event)"
                        style="overflow:hidden; resize:none; font-size:0.8rem; margin-bottom:0.5rem;"></textarea>
                    <label style="display:block; font-size:0.75rem; font-weight:600; color:#1B5E20; margin-bottom:0.25rem;">Video / Meeting URLs (mỗi dòng 1 link)</label>
                    <textarea wire:model="editTaskVideo" class="input" rows="3" placeholder="https://youtube.com/watch?v=...&#10;https://meet.google.com/...&#10;https://youtube.com/watch?v=..."
                        x-data x-init="$el.style.height = $el.scrollHeight + 'px'"
                        @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                        style="overflow:hidden; resize:none; font-size:0.8rem; margin-bottom:0.5rem;"></textarea>
                    <label style="display:block; font-size:0.75rem; font-weight:600; color:#1B5E20; margin-bottom:0.25rem;">Lịch Meeting (giờ VN)</label>
                    <input wire:model="editTaskMeetingAt" type="datetime-local" class="input" style="font-size:0.8rem; margin-bottom:0.5rem;">
                    <label style="display:block; font-size:0.75rem; font-weight:600; color:#1B5E20; margin-bottom:0.25rem;">SOP / Hướng dẫn</label>
                    <textarea wire:model="editTaskSop" class="input" rows="4" placeholder="Viết hướng dẫn chi tiết..."
                        x-data x-init="$el.style.height = $el.scrollHeight + 'px'"
                        @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                        @paste="window.pasteAsMarkdown($event)"
                        style="overflow:hidden; resize:none; font-size:0.8rem; margin-bottom:0.5rem;"></textarea>
                    <label style="display:block; font-size:0.75rem; font-weight:600; color:#1B5E20; margin-bottom:0.25rem;">Yêu cầu bằng chứng</label>
                    <textarea wire:model="editTaskEvidenceLabel" class="input" rows="2" placeholder="VD: Chụp ảnh màn hình các tool đã cài"
                        x-data x-init="$el.style.height = $el.scrollHeight + 'px'"
                        @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                        style="overflow:hidden; resize:none; font-size:0.8rem; margin-bottom:0.5rem;"></textarea>
                    <label style="display:block; font-size:0.75rem; font-weight:600; color:#1B5E20; margin-bottom:0.25rem;">Nhắn nhủ cho thành viên</label>
                    <textarea wire:model="editTaskAdminNote" class="input" rows="2" placeholder="Lời nhắn từ admin hiện sau khi hoàn thành nhiệm vụ..." style="font-size:0.8rem; margin-bottom:0.5rem;"
                        x-data x-init="$el.style.height = $el.scrollHeight + 'px'"
                        @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                        style="overflow:hidden; resize:none; font-size:0.8rem; margin-bottom:0.5rem;"></textarea>

                    {{-- Quiz JSON editor --}}
                    <label style="display:block; font-size:0.75rem; font-weight:600; color:#1B5E20; margin-bottom:0.25rem;">
                        📝 Quiz (JSON) <span style="font-weight:400; color:#5C5C66;">— để trống nếu task này không có quiz</span>
                    </label>
                    <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:0.375rem; padding:0.5rem 0.625rem; margin-bottom:0.375rem; font-size:0.7rem; color:#78716C; line-height:1.5;">
                        Mỗi phần tử cần có: <code style="background:#FEF3C7; padding:0.05rem 0.25rem; border-radius:3px;">q</code>, <code style="background:#FEF3C7; padding:0.05rem 0.25rem; border-radius:3px;">options{A,B,C,D}</code>, <code style="background:#FEF3C7; padding:0.05rem 0.25rem; border-radius:3px;">correct</code> (A/B/C/D), <code style="background:#FEF3C7; padding:0.05rem 0.25rem; border-radius:3px;">explanation</code>, <code style="background:#FEF3C7; padding:0.05rem 0.25rem; border-radius:3px;">ai_prompt</code> (giữ key cũ để tương thích). Với DSCons, dùng field này cho gợi ý đối chiếu quy trình kỹ thuật. Mỗi câu đúng = +2 XP cho user.
                    </div>
                    <textarea wire:model="editTaskQuizJson" rows="10" class="input" placeholder='[{"q":"Câu hỏi 1","options":{"A":"...","B":"...","C":"...","D":"..."},"correct":"A","explanation":"Giải thích...","ai_prompt":"Gợi ý đối chiếu quy trình kỹ thuật..."}]'
                        style="font-family:monospace; font-size:0.72rem; line-height:1.5; resize:vertical; min-height:140px; margin-bottom:0.5rem;"></textarea>
                    @error('editTaskQuizJson') <p style="color:#DC2626; font-size:0.7rem; margin-bottom:0.5rem;">{{ $message }}</p> @enderror

                    <div class="flex gap-2">
                        <button wire:click="saveEditTask" class="btn btn-primary" style="font-size:0.75rem; padding:0.25rem 0.625rem;">Lưu</button>
                        <button wire:click="cancelEditTask" class="btn btn-ghost" style="font-size:0.75rem; padding:0.25rem 0.625rem;">Hủy</button>
                    </div>
                </div>
                @endif

                {{-- Expanded content (server-side gated: locked tasks render nothing to avoid view-source leak) --}}
                <div x-show="expanded" x-transition style="margin-top:0.75rem; margin-left:2rem;">
                    @if($isUnlocked || $isCompleted)

                    @if($task->description)
                    <div class="prose-task" style="font-size:0.8rem; color:#5C5C66; line-height:1.6; margin-bottom:0.75rem;">{!! Str::markdown($task->description, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
                    @endif

                    {{-- Videos / Links --}}
                    @if($task->video_url)
                    <div class="flex flex-col gap-2" style="margin-bottom:0.75rem;">
                        @foreach(array_filter(preg_split('/[\r\n]+/', $task->video_url)) as $videoUrl)
                        @php
                            $videoUrl = trim($videoUrl);
                            if (!$videoUrl) continue;
                            $ytId = null;
                            if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([a-zA-Z0-9_-]{11})/', $videoUrl, $m)) {
                                $ytId = $m[1];
                            }
                            $isMeet = str_contains($videoUrl, 'meet.google.com');
                        @endphp
                        @if($ytId)
                        <div style="position:relative; padding-bottom:56.25%; height:0; overflow:hidden; border-radius:0.5rem;">
                            <iframe src="https://www.youtube.com/embed/{{ $ytId }}" style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                        @elseif($isMeet)
                        <a href="{{ $videoUrl }}" target="_blank" rel="noopener" class="flex items-center gap-2" style="font-size:0.8rem; font-weight:600; padding:0.625rem; border-radius:0.5rem; color:#1F77BE; background:#E1F4F7; border:1px solid #B8D7E6;">
                            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24"><path d="M14.5 10.5L18.2 7.4C18.5 7.2 19 7.4 19 7.8V16.2C19 16.6 18.5 16.8 18.2 16.6L14.5 13.5V10.5Z" fill="#00832D"/><rect x="3" y="6" width="12" height="12" rx="1.5" fill="#00AC47"/><path d="M14.5 10.5L18.2 7.4C18.5 7.2 19 7.4 19 7.8V12H14.5V10.5Z" fill="#00832D"/><path d="M3 14.5H9V18H4.5C3.67 18 3 17.33 3 16.5V14.5Z" fill="#0066DA"/><path d="M9 14.5H14.5V18H9V14.5Z" fill="#00AC47"/><path d="M9 6H14.5V10.5H9V6Z" fill="#FFBA00"/><path d="M3 7.5C3 6.67 3.67 6 4.5 6H9V10.5H3V7.5Z" fill="#0066DA"/><path d="M3 10.5H9V14.5H3V10.5Z" fill="#0066DA"/></svg>
                            Tham gia Google Meet
                        </a>
                        @else
                        <a href="{{ $videoUrl }}" target="_blank" rel="noopener" class="flex items-center gap-2" style="font-size:0.8rem; font-weight:600; padding:0.625rem; border-radius:0.5rem; color:#d17856; background:#F0FDF4; border:1px solid #A5D6A7;">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.84A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.27l9.344-5.891a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                            {{ Str::limit($videoUrl, 50) }}
                        </a>
                        @endif
                        @endforeach
                    </div>
                    @endif

                    {{-- SOP --}}
                    {{-- Structured curriculum owns the learner-facing SOP. Keep the legacy
                         field for old tasks/data, but do not render it a second time. --}}
                    @if($task->sop_content && empty($task->instruction_payload))
                    <div style="background:#F7F5F3; border-radius:0.5rem; padding:0.75rem; margin-bottom:0.75rem; border-left:3px solid #d17856;">
                        <p style="font-size:0.7rem; font-weight:700; color:#1B5E20; margin-bottom:0.375rem;">▤ SOP — Hướng dẫn</p>
                        <div class="prose-task" style="font-size:0.8rem; color:#2E2E2E; line-height:1.6;">{!! Str::markdown($task->sop_content, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
                    </div>
                    @endif

                    {{-- Quiz (when task has quiz_json defined) --}}
                    @if(!empty($task->quiz_json))
                    <livewire:quiz-section :task="$task" :wire:key="'quiz-'.$task->id" />
                    @endif

                    @php
                        $curriculum = is_array($task->instruction_payload) ? $task->instruction_payload : [];
                        $myTaskStatus = $myCompletions[$task->id]->status ?? null;
                        $modalityLabel = match ($curriculum['modality'] ?? '') {
                            'live' => 'Livestream',
                            'video' => 'Video ngắn',
                            'assignment' => 'Bài thực hành',
                            default => 'Bài học',
                        };
                        $trackLabel = match ($curriculum['track'] ?? 'common') {
                            'common' => 'Nền tảng chung',
                            'capstone' => 'Tool của bạn',
                            'wpf', 'ui' => 'Giao diện tool',
                            default => 'Thực hành',
                        };
                    @endphp
                    @if($curriculum)
                    <section class="challenge-curriculum" aria-labelledby="curriculum-{{ $task->id }}">
                        <div class="challenge-curriculum-head">
                            <div>
                                <span class="challenge-eyebrow">{{ $modalityLabel }} · {{ $curriculum['estimated_minutes'] ?? 0 }} phút</span>
                                <h3 id="curriculum-{{ $task->id }}">Lộ trình học ngày {{ $task->day_number }}</h3>
                            </div>
                            <span class="challenge-track">{{ $trackLabel }}</span>
                        </div>
                        @if(!empty($curriculum['why']))
                        <p class="challenge-curriculum-why"><strong>Vì sao kỹ sư MEP cần?</strong> {{ $curriculum['why'] }}</p>
                        @endif
                        <div class="challenge-curriculum-grid">
                            <div>
                                <h4>Hôm nay cần đạt</h4>
                                <p><strong>{{ $curriculum['required_outcome'] ?? '' }}</strong></p>
                                @if(!empty($curriculum['learning_objectives']))
                                <ul>
                                    @foreach($curriculum['learning_objectives'] as $objective)
                                    <li>{{ $objective }}</li>
                                    @endforeach
                                </ul>
                                @endif
                            </div>
                            <div>
                                <h4>AI thực hiện</h4>
                                <ul>
                                    @foreach($curriculum['ai_actions'] ?? [] as $action)
                                    <li>{{ $action }}</li>
                                    @endforeach
                                </ul>
                                <h4>Học viên thực hiện</h4>
                                <ul>
                                    @foreach($curriculum['student_actions'] ?? [] as $action)
                                    <li>{{ $action }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <details class="challenge-curriculum-details" open>
                            <summary>SOP duy nhất — làm theo từng bước</summary>
                            <ol>
                                @foreach($curriculum['sop_steps'] ?? [] as $step)
                                <li>{{ $step }}</li>
                                @endforeach
                            </ol>
                        </details>
                        @if(!empty($curriculum['ai_prompt']))
                        <details class="challenge-curriculum-details">
                            <summary>Prompt copy vào AI Agent</summary>
                            <div class="challenge-prompt-box" x-data="{ copied: false }">
                                <div class="challenge-prompt-title"><strong>Prompt của ngày {{ $task->day_number }}</strong><button type="button" @click="navigator.clipboard.writeText($refs.prompt.innerText); copied = true; setTimeout(() => copied = false, 1600)" aria-label="Sao chép prompt"><span x-show="!copied">Sao chép</span><span x-show="copied" x-cloak>Đã sao chép</span></button></div>
                                <pre x-ref="prompt">{{ $curriculum['ai_prompt'] }}</pre>
                            </div>
                        </details>
                        @endif
                        @if(!$isCompleted || $myTaskStatus === 'rejected')
                        <div class="challenge-submission-guide">
                            <h4>Minh chứng cần nộp</h4>
                            @foreach($curriculum['verification_checklist'] ?? [] as $index => $check)
                            <label><input type="checkbox" value="{{ $index }}" wire:model="structuredSubmission.{{ $task->id }}.checklist.{{ $index }}"> <span>{{ $check }}</span></label>
                            @endforeach
                            <p class="challenge-evidence-hint">Bạn nộp ảnh hoặc dán link ảnh ở ô nộp bài bên dưới. Ảnh phải cho thấy kết quả thật trong phần mềm, không chỉ là code.</p>
                        </div>
                        @endif
                        @if(!empty($curriculum['share_to_feed']))
                        <p class="challenge-share-note"><strong>Chia sẻ lên Bảng tin:</strong> {{ $curriculum['share_to_feed'] }}</p>
                        @endif
                        <div class="challenge-rubric-preview">
                            <strong>Đạt từ {{ $curriculum['pass_score'] ?? 70 }}/100</strong>
                            <span>Đủ bằng chứng 30</span><span>Kết quả đúng 40</span><span>Cá nhân hóa 15</span><span>Kiểm chứng 15</span>
                            <small>Critical Fail: chỉ nộp ảnh code, không có kết quả Revit, sửa model gốc, không thể đối chiếu hoặc không cài lại được.</small>
                        </div>
                    </section>
                    @endif

                    {{-- Evidence: show submission or form (cùng UI cho contest & non-contest cho phần "bài chính ngày 15") --}}
                    @if($isCompleted)
                    @php
                        $myEvidence = $myCompletions[$task->id] ?? null;
                        $evidenceIsRejected = $myEvidence && $myEvidence->status === 'rejected';
                    @endphp
                    @if($myEvidence)
                    @php
                        $evidenceIsPending = $myEvidence->status === 'pending';
                        $evBg = $evidenceIsRejected ? '#FEF2F2' : ($evidenceIsPending ? '#FFFBEB' : '#F0FDF4');
                        $evBd = $evidenceIsRejected ? '#FECACA' : ($evidenceIsPending ? '#FDE68A' : '#A7F3D0');
                        $evCl = $evidenceIsRejected ? '#DC2626' : ($evidenceIsPending ? '#92400E' : '#065F46');
                        $evLb = $evidenceIsRejected ? '✗ Bài bị từ chối' : ($evidenceIsPending ? '⧗ Đang chờ admin duyệt' : '✓ Bài đã được duyệt');
                    @endphp
                    <div style="background:{{ $evBg }}; border:1px solid {{ $evBd }}; border-radius:0.5rem; padding:0.75rem;">
                        <p style="font-size:0.7rem; font-weight:600; color:{{ $evCl }}; margin-bottom:0.375rem;">
                            {{ $evLb }}
                        </p>
                        <div style="font-size:0.8rem; color:#2E2E2E; line-height:1.5; white-space:pre-line;">{!! preg_replace('/(https?:\/\/(?:(?!&quot;|&lt;|&gt;)[^\s])+)/', '<a href="$1" target="_blank" rel="noopener" style="color:#d17856; text-decoration:underline;">$1</a>', e($myEvidence->evidence)) !!}</div>
                        @if($evidenceIsRejected && $myEvidence->review_note)
                        <div style="background:#FFF; border:1px solid #FECACA; border-radius:0.375rem; padding:0.5rem; margin-top:0.5rem;">
                            <p style="font-size:0.7rem; font-weight:600; color:#DC2626; margin-bottom:0.125rem;">Lý do từ chối:</p>
                            <p style="font-size:0.8rem; color:#2E2E2E;">{{ $myEvidence->review_note }}</p>
                        </div>
                        @endif
                        @php
                            $resubmitted = \Carbon\Carbon::parse($myEvidence->updated_at)->gt(\Carbon\Carbon::parse($myEvidence->created_at)->copy()->addSeconds(60));
                        @endphp
                        <p style="font-size:0.65rem; color:#5C5C66; margin-top:0.375rem;">
                            Nộp lúc {{ \Carbon\Carbon::parse($myEvidence->created_at)->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m') }}
                            @if($myEvidence->is_late && !$resubmitted) · <span style="color:#D97706;">Trễ</span> @endif
                            @if($resubmitted)
                            <br>Nộp lại lúc {{ \Carbon\Carbon::parse($myEvidence->updated_at)->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m') }}
                            @if($myEvidence->is_late) · <span style="color:#D97706;">Trễ</span> @endif
                            @endif
                        </p>
                    </div>
                    {{-- Resubmit form for rejected tasks --}}
                    @if($evidenceIsRejected)
                    <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:0.5rem; padding:0.75rem; margin-top:0.5rem;">
                        <p style="font-size:0.75rem; font-weight:600; color:#92400E; margin-bottom:0.375rem;">
                            Nộp lại bài
                        </p>
                        <textarea wire:model="taskEvidence.{{ $task->id }}" class="input" rows="3"
                            placeholder="Mô tả + paste link bằng chứng mới..."
                            x-data x-init="$el.style.height = $el.scrollHeight + 'px'"
                            @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                            style="overflow:hidden; resize:none; font-size:0.8rem;"></textarea>
                        <button type="button" wire:click="resubmitTask({{ $task->id }})"
                                wire:loading.attr="disabled"
                                wire:target="resubmitTask({{ $task->id }})"
                                class="btn btn-primary mt-2"
                                style="font-size:0.8rem; padding:0.3rem 0.875rem;">
                            <span wire:loading.remove wire:target="resubmitTask({{ $task->id }})">Nộp lại</span>
                            <span wire:loading wire:target="resubmitTask({{ $task->id }})">Đang nộp...</span>
                        </button>
                    </div>
                    @endif
                    @endif
                    @elseif($isFrozen)
                    <div style="background:#FEF3C7; border:1px solid #FDE68A; border-radius:0.5rem; padding:0.75rem;">
                        <p style="font-size:0.8rem; font-weight:700; color:#92400E; margin:0 0 0.25rem;">⏸ Nhiệm vụ đang tạm dừng</p>
                        <p style="font-size:0.75rem; color:#78716C; margin:0;">Tiếp tục vào ngày {{ $expedition->freeze_ends_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y') }}. Timer được đóng băng — bạn sẽ không bị tính trễ.</p>
                    </div>
                    @elseif($isUnlocked)
                    <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:0.5rem; padding:0.75rem;">
                        <div style="font-size:0.75rem; font-weight:600; color:#92400E; margin-bottom:0.25rem; line-height:1.7;">
                            @php
                                $evidenceHtml = '';
                                if ($task->evidence_label) {
                                    $evidenceHtml = e($task->evidence_label);
                                    $evidenceHtml = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $evidenceHtml);
                                    $evidenceHtml = preg_replace('/`(.+?)`/', '<code style="background:#FEF3C7;padding:0.1rem 0.3rem;border-radius:3px;font-size:0.7rem;">$1</code>', $evidenceHtml);
                                    $evidenceHtml = nl2br($evidenceHtml);
                                }
                            @endphp
                            ▣ Nộp bài{!! $evidenceHtml ? ': ' . $evidenceHtml : '' !!}
                        </div>
                        <p style="font-size:0.7rem; color:#5C5C66; margin-bottom:0.5rem;">
                            Hãy dán link ảnh hoặc mô tả ngắn cho ảnh minh chứng. Ảnh phải là kết quả thật trong phần mềm, không chỉ là code.
                        </p>
                        <textarea wire:model="taskEvidence.{{ $task->id }}" class="input" rows="3"
                            placeholder="Mô tả những gì bạn đã làm, paste link bằng chứng nếu có..."
                            x-data x-init="$el.style.height = $el.scrollHeight + 'px'"
                            @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                            style="overflow:hidden; resize:none; font-size:0.8rem;"></textarea>
                        <button wire:click="completeTask({{ $task->id }})"
                                wire:loading.attr="disabled"
                                wire:target="completeTask({{ $task->id }})"
                                class="btn btn-primary mt-2" style="font-size:0.8rem; padding:0.3rem 0.875rem;">
                            <span wire:loading.remove wire:target="completeTask({{ $task->id }})">Nộp bài & Hoàn thành</span>
                            <span wire:loading wire:target="completeTask({{ $task->id }})">Đang nộp...</span>
                        </button>
                    </div>
                    @endif

                    {{-- ─── Mini-game (contest extension) — chỉ hiện sau khi đã nộp bài chính của ngày đó ─── --}}
                    @if($task->is_contest && $isUnlocked && !$isFrozen && ($myCompletions[$task->id] ?? null))
                    @php
                        $allSubsCt = $myAllCompletions[$task->id] ?? collect();
                        $miniRows = $allSubsCt->slice(1)->values();
                        $approvedRows = $miniRows->where('status', 'approved')->values();
                        $latestMini = $miniRows->last();
                        $hasPending = $latestMini && $latestMini->status === 'pending';
                        $hasRejected = $latestMini && $latestMini->status === 'rejected';
                        $approvedCount = $approvedRows->count();
                        $daySubStatus = $myCompletions[$task->id]->status ?? '';
                        $daySubRejected = $daySubStatus === 'rejected';
                        $daySubPending = $daySubStatus === 'pending';
                        $daySubApproved = $daySubStatus === 'approved';
                        $miniDeadline = $contestEnds[$task->id] ?? ($taskDeadlines[$task->id] ?? null);
                        $miniExpired = $miniDeadline && now()->greaterThan($miniDeadline);
                    @endphp
                    <div style="background:#FFF8E9; border:1px solid #F59E0B; border-radius:0.5rem; padding:0.75rem; margin-top:0.75rem;">
                        <div class="flex items-center justify-between" style="margin-bottom:0.4rem;">
                            <div style="font-size:0.85rem; font-weight:700; color:#78350F; display:flex; align-items:center; gap:0.4rem;">
                                {{ $task->contest_title ?? 'Mini-game' }}
                            </div>
                            @if($approvedCount > 0)
                            <span style="font-size:0.72rem; font-weight:700; color:#065F46; background:#D1FAE5; padding:0.15rem 0.5rem; border-radius:999px;">
                                {{ $approvedCount }} ứng dụng đã duyệt
                            </span>
                            @endif
                        </div>
                        <p style="font-size:0.78rem; color:#78350F; line-height:1.6; margin:0 0 0.65rem;">
                            {!! $task->contest_description
                                ? nl2br(e($task->contest_description))
                                : 'Nộp bài chính xong là bạn vào sân. Từ giờ tới hạn mini-game, nộp thêm bao nhiêu bài cũng được — mỗi lần 1 bài để chờ duyệt. Càng nhiều bài được duyệt, cơ hội thắng càng cao; hết hạn, ai có nhiều bài được duyệt nhất sẽ giành chiến thắng. Admin đặt tiêu đề, mô tả và thời lượng mini-game trong phần chỉnh sửa nhiệm vụ.' !!}
                        </p>

                        @if($miniRows->isNotEmpty())
                        <div style="display:flex; flex-direction:column; gap:0.4rem; margin-bottom:0.5rem;">
                            @foreach($miniRows as $sub)
                            @php
                                $st = $sub->status;
                                $bg = $st === 'approved' ? '#F0FDF4' : ($st === 'rejected' ? '#FEF2F2' : '#FFFFFF');
                                $bd = $st === 'approved' ? '#A7F3D0' : ($st === 'rejected' ? '#FECACA' : '#FDE68A');
                                $cl = $st === 'approved' ? '#065F46' : ($st === 'rejected' ? '#DC2626' : '#78350F');
                                $lb = $st === 'approved' ? '✓ Đã duyệt' : ($st === 'rejected' ? '✗ Bị từ chối' : '⧗ Chờ duyệt');
                            @endphp
                            <div style="background:{{ $bg }}; border:1px solid {{ $bd }}; border-radius:0.4rem; padding:0.5rem 0.65rem;">
                                <div class="flex items-center justify-between" style="margin-bottom:0.2rem;">
                                    <p style="font-size:0.7rem; font-weight:600; color:{{ $cl }}; margin:0;">
                                        {{ $lb }} · Ứng dụng #{{ $loop->iteration }}
                                    </p>
                                    <p style="font-size:0.65rem; color:#5C5C66; margin:0;">
                                        {{ \Carbon\Carbon::parse($sub->created_at)->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m') }}
                                    </p>
                                </div>
                                <div style="font-size:0.78rem; color:#2E2E2E; line-height:1.5; white-space:pre-line;">{!! preg_replace('/(https?:\/\/(?:(?!&quot;|&lt;|&gt;)[^\s])+)/', '<a href="$1" target="_blank" rel="noopener" style="color:#d17856; text-decoration:underline;">$1</a>', e($sub->evidence)) !!}</div>
                                @if($st === 'rejected' && $sub->review_note)
                                <p style="font-size:0.65rem; color:#DC2626; margin-top:0.2rem;">Lý do: {{ $sub->review_note }}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif

                        @if($miniExpired)
                        {{-- contest ended: submit form hidden, no message needed --}}
                        @elseif($daySubPending)
                        <p style="font-size:0.72rem; color:#78350F; opacity:0.85; margin:0;">
                            ⏳ Bài chính đang chờ admin duyệt — sau khi được duyệt bạn mới tham gia mini-game được.
                        </p>
                        @elseif($daySubRejected)
                        <p style="font-size:0.72rem; color:#78350F; opacity:0.8; font-style:italic; margin:0;">
                            Bài chính ngày 15 đang bị từ chối. Hãy chỉnh và nộp lại bài chính ở trên trước khi tham gia mini-game.
                        </p>
                        @elseif($hasPending)
                        <p style="font-size:0.72rem; color:#78350F; margin:0;">
                            ⏳ Ứng dụng đang chờ admin duyệt — đợi duyệt xong rồi bạn nộp ứng dụng tiếp được.
                        </p>
                        @else
                        <textarea wire:model="taskEvidence.{{ $task->id }}" class="input" rows="3"
                            placeholder="Mô tả ứng dụng + paste link bằng chứng..."
                            x-data x-init="$el.style.height = $el.scrollHeight + 'px'"
                            @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                            style="overflow:hidden; resize:none; font-size:0.78rem; background:#FFF;"></textarea>
                        <button wire:click="submitMiniGame({{ $task->id }})"
                                wire:loading.attr="disabled"
                                wire:target="submitMiniGame({{ $task->id }})"
                                class="btn btn-primary mt-2" style="font-size:0.78rem; padding:0.3rem 0.85rem;">
                            <span wire:loading.remove wire:target="submitMiniGame({{ $task->id }})">
                                @if($hasRejected)
                                    Nộp lại ứng dụng
                                @elseif($miniRows->isEmpty())
                                    Nộp ứng dụng cho mini-game
                                @else
                                    Nộp ứng dụng tiếp
                                @endif
                            </span>
                            <span wire:loading wire:target="submitMiniGame({{ $task->id }})">Đang nộp...</span>
                        </button>
                        @endif
                    </div>
                    @endif
                    @endif {{-- end isUnlocked || isCompleted --}}
                </div>
            </div>
            {{-- Tài liệu thưởng: hiện sau khi bài được duyệt --}}
            @if($task->reward_file_path && $isCompleted && ($myCompletions[$task->id]->status ?? '') === 'approved')
            <div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:0.5rem; padding:0.75rem; margin:0.5rem 0; display:flex; align-items:center; gap:0.75rem;">
                <span style="font-size:1.5rem;">📥</span>
                <div style="flex:1; min-width:0;">
                    <p style="font-size:0.8rem; font-weight:700; color:#1E40AF; margin:0;">Tài liệu thưởng — Ngày {{ $task->day_number }}</p>
                    <p style="font-size:0.7rem; color:#3B82F6; margin:0; line-height:1.4;">{{ $task->reward_file_label ?: 'Tải về để giữ làm tham khảo riêng.' }}</p>
                </div>
                <a href="{{ route('challenge.reward.download', ['slug' => $expedition->slug, 'day' => $task->day_number]) }}" style="background:#1D4ED8; color:#fff; font-size:0.75rem; font-weight:600; padding:0.45rem 0.85rem; border-radius:0.45rem; text-decoration:none; white-space:nowrap; display:inline-flex; align-items:center; gap:0.35rem;">⬇ Tải về</a>
            </div>
            @endif

            {{-- Lời nhắn từ Host Challenge: hiện sau khi user hoàn thành & được duyệt (đồng bộ mọi day, kể cả contest) --}}
            @if($task->admin_note && $isCompleted && ($myCompletions[$task->id]->status ?? '') === 'approved')
            <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:0.5rem; padding:0.75rem; margin:0.5rem 0;">
                <p style="font-size:0.7rem; font-weight:700; color:#92400E; margin-bottom:0.25rem;">Lời nhắn từ Host Challenge</p>
                <div style="font-size:0.8rem; color:#2E2E2E; line-height:1.6; white-space:pre-line;">{{ $task->admin_note }}</div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @elseif($tasks->count() > 0 && !$isApproved)
    {{-- Locked: non-member can't see content --}}
    <div class="card mb-4 text-center" style="padding:2rem 1rem;">
        <p style="font-size:2rem; margin-bottom:0.5rem;">▪</p>
        <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A; margin-bottom:0.25rem;">{{ $tasks->count() }} nhiệm vụ trong {{ $expedition->required_days }} ngày</p>
        <p style="font-size:0.8rem; color:#5C5C66;">Đăng ký và được duyệt để mở khóa nội dung bài tập</p>
    </div>
    @endif

    {{-- Video feedback moved to header card above --}}

    {{-- Admin: Video Feedback Review --}}
                @can('community-admin')
    @php
        $pendingVideos = $approvedMembers->filter(fn($m) => $m->video_feedback_status === 'pending');
    @endphp
    @if($pendingVideos->count() > 0)
    <div class="card mb-4" style="border-left:3px solid #D97706;">
        <h2 style="font-size:0.9rem; font-weight:700; color:#92400E; margin-bottom:0.75rem;">▶ Video Feedback chờ duyệt ({{ $pendingVideos->count() }})</h2>
        @foreach($pendingVideos as $vm)
        <div class="py-2 {{ !$loop->last ? 'border-b' : '' }}" style="{{ !$loop->last ? 'border-color:#E8E4DE;' : '' }}">
            <div class="flex items-center gap-2 mb-1">
                <img src="{{ $vm->user->avatar_url }}" class="avatar w-6 h-6" alt="">
                <span style="font-size:0.8rem; font-weight:600;">{{ $vm->user->name }}</span>
                <span style="font-size:0.65rem; color:#5C5C66;">{{ $vm->video_feedback_at ? \Carbon\Carbon::parse($vm->video_feedback_at)->timezone('Asia/Ho_Chi_Minh')->diffForHumans() : '' }}</span>
            </div>
            <a href="{{ $vm->video_feedback_url }}" target="_blank" rel="noopener" style="font-size:0.75rem; color:#d17856; text-decoration:underline; display:block; margin-bottom:0.375rem;">{{ Str::limit($vm->video_feedback_url, 60) }}</a>
            <div x-data="{ showReject: false, note: '' }" class="flex flex-wrap gap-1">
                <button wire:click="approveVideoFeedback({{ $vm->id }})" class="btn btn-primary" style="font-size:0.7rem; padding:0.2rem 0.5rem;">✓ Duyệt</button>
                <button @click="showReject = !showReject" class="btn btn-ghost" style="font-size:0.7rem; padding:0.2rem 0.5rem; color:#991B1B;">✗ Từ chối</button>
                <div x-show="showReject" x-transition style="width:100%; margin-top:0.25rem;">
                    <input x-model="note" type="text" class="input" placeholder="Lý do..." style="font-size:0.75rem; margin-bottom:0.25rem;">
                    <button @click="$wire.rejectVideoFeedback({{ $vm->id }}, note); showReject = false" class="btn btn-danger" style="font-size:0.7rem; padding:0.2rem 0.5rem;">Xác nhận từ chối</button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
    @endcan

    {{-- Admin: Member Report --}}
                @can('community-admin')
    @if($memberReport->count() > 0)
    <div class="card mb-4">
        <div class="flex items-center justify-between mb-3">
            <h2 style="font-size:0.9rem; font-weight:700; color:#1B5E20;">▥ Báo cáo thành viên ({{ $reportTotal }})</h2>
            <input wire:model.live.debounce.300ms="reportSearch" type="text" placeholder="Tìm tên..." class="input" style="font-size:0.75rem; padding:0.25rem 0.5rem; width:160px;">
        </div>

        {{-- Dashboard thống kê --}}
        @if($reportStats)
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:0.5rem; margin-bottom:1rem;">
            <div style="background:#F0FDF4; border-radius:0.5rem; padding:0.5rem 0.75rem; text-align:center;">
                <div style="font-size:1.1rem; font-weight:700; color:#d17856;">{{ $reportStats->completed }}</div>
                <div style="font-size:0.65rem; color:#5C5C66;">Hoàn thành</div>
            </div>
            <div style="background:#F0FDF4; border-radius:0.5rem; padding:0.5rem 0.75rem; text-align:center;">
                <div style="font-size:1.1rem; font-weight:700; color:#059669;">{{ $reportStats->on_track }}</div>
                <div style="font-size:0.65rem; color:#5C5C66;">Đúng tiến độ</div>
            </div>
            <div style="background:#FFF7ED; border-radius:0.5rem; padding:0.5rem 0.75rem; text-align:center;">
                <div style="font-size:1.1rem; font-weight:700; color:#D97706;">{{ $reportStats->on_track_late }}</div>
                <div style="font-size:0.65rem; color:#5C5C66;">Đúng tiến độ có trễ</div>
            </div>
            <div style="background:#FEF2F2; border-radius:0.5rem; padding:0.5rem 0.75rem; text-align:center;">
                <div style="font-size:1.1rem; font-weight:700; color:#DC2626;">{{ $reportStats->miss }}</div>
                <div style="font-size:0.65rem; color:#5C5C66;">Miss</div>
            </div>
        </div>
        @endif

        <div style="overflow-x:auto;">
            <table style="width:100%; font-size:0.8rem; border-collapse:collapse; white-space:nowrap;">
                <thead>
                    <tr style="border-bottom:2px solid #E1E1E1;">
                        <th style="text-align:left; padding:0.5rem 0.375rem; color:#5C5C66; font-size:0.7rem;">Thành viên</th>
                        <th style="text-align:center; padding:0.5rem 0.375rem; color:#5C5C66; font-size:0.7rem;">Ngày</th>
                        <th style="text-align:center; padding:0.5rem 0.375rem; color:#5C5C66; font-size:0.7rem;">Hoàn thành</th>
                        <th style="text-align:center; padding:0.5rem 0.375rem; color:#5C5C66; font-size:0.7rem;">Từ chối</th>
                        <th style="text-align:center; padding:0.5rem 0.375rem; color:#5C5C66; font-size:0.7rem;">Trễ</th>
                        <th style="text-align:center; padding:0.5rem 0.375rem; color:#5C5C66; font-size:0.7rem;">Miss</th>
                        <th style="text-align:center; padding:0.5rem 0.375rem; color:#5C5C66; font-size:0.7rem;">Tiến độ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($memberReport as $r)
                    <tr style="border-bottom:1px solid #E1E1E1;">
                        <td style="padding:0.5rem 0.375rem; max-width:200px;">
                            <div class="flex items-center gap-2" style="min-width:0;">
                                <img src="{{ $r->member->user->avatar_url }}" class="avatar w-6 h-6 flex-shrink-0" alt="">
                                <a href="{{ route('profile', $r->member->user->username ?? $r->member->user->id) }}" style="font-weight:600; color:#1A1A1A; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $r->member->user->name }}">{{ $r->member->user->name }}</a>
                            </div>
                        </td>
                        <td style="text-align:center; padding:0.5rem 0.375rem; color:#5C5C66;">{{ $r->current_day }}/{{ $r->total }}</td>
                        <td style="text-align:center; padding:0.5rem 0.375rem; color:#059669; font-weight:600;">{{ $r->completed }}</td>
                        <td style="text-align:center; padding:0.5rem 0.375rem; {{ $r->rejected > 0 ? 'color:#DC2626; font-weight:600;' : 'color:#5C5C66;' }}">{{ $r->rejected }}</td>
                        <td style="text-align:center; padding:0.5rem 0.375rem; {{ $r->late > 0 ? 'color:#D97706; font-weight:600;' : 'color:#5C5C66;' }}">{{ $r->late }}</td>
                        <td style="text-align:center; padding:0.5rem 0.375rem; {{ $r->missed > 0 ? 'color:#DC2626; font-weight:700;' : 'color:#5C5C66;' }}">{{ $r->missed }}</td>
                        <td style="padding:0.5rem 0.375rem;">
                            <div style="display:flex; align-items:center; gap:0.375rem;">
                                <div style="flex:1; height:6px; background:#EEECE9; border-radius:3px; overflow:hidden;">
                                    <div style="height:100%; border-radius:3px; width:{{ $r->pct }}%; {{ $r->missed > 0 ? 'background:#DC2626;' : 'background:#059669;' }}"></div>
                                </div>
                                <span style="font-size:0.65rem; color:#5C5C66; white-space:nowrap;">{{ $r->pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($reportLastPage > 1)
        <div class="flex items-center justify-between mt-3" style="font-size:0.75rem;">
            <span style="color:#5C5C66;">Trang {{ $reportPage }}/{{ $reportLastPage }}</span>
            <div class="flex gap-1">
                <button wire:click="$set('reportPage', {{ max(1, $reportPage - 1) }})" class="btn btn-ghost" style="font-size:0.65rem; padding:0.2rem 0.5rem;" @if($reportPage <= 1) disabled style="opacity:0.4; font-size:0.65rem; padding:0.2rem 0.5rem;" @endif>← Trước</button>
                <button wire:click="$set('reportPage', {{ min($reportLastPage, $reportPage + 1) }})" class="btn btn-ghost" style="font-size:0.65rem; padding:0.2rem 0.5rem;" @if($reportPage >= $reportLastPage) disabled style="opacity:0.4; font-size:0.65rem; padding:0.2rem 0.5rem;" @endif>Sau →</button>
            </div>
        </div>
        @endif
    </div>
    @endif
    @endcan

    {{-- Submission history --}}
    @if($submissions && ($submissions->count() > 0 || $submissionSearch))
    <div class="card">
                @can('community-admin')
        <input wire:model.live.debounce.300ms="submissionSearch" type="search" class="input mb-3" placeholder="Tìm theo tên, email, username..." style="font-size:0.8rem; max-width:350px;">
        @endcan
        <div class="flex items-center justify-between mb-3">
            <h2 style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">Lịch sử nộp bài ({{ $submissions->total() }})</h2>
                @can('community-admin')
            @if($totalPendingSubmissions > 0)
            <button wire:click="approveAllPending" wire:confirm="Duyệt tất cả bài đang chờ?" class="btn btn-primary" style="font-size:0.7rem; padding:0.25rem 0.625rem;">
                ✓ Duyệt tất cả ({{ $totalPendingSubmissions }})
            </button>
            @endif
            @endcan
        </div>
        @foreach($submissions as $sub)
        <div class="py-3 {{ !$loop->last ? 'border-b' : '' }}" style="{{ !$loop->last ? 'border-color:#E1E1E1;' : '' }}">
            <div class="mb-1">
                @php $avatarUrl = $sub->avatar ? asset('storage/' . $sub->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($sub->name) . '&background=7C3AED&color=fff&size=64'; @endphp
                @php $subResubmittedAt = \Carbon\Carbon::parse($sub->updated_at)->gt(\Carbon\Carbon::parse($sub->created_at)->copy()->addSeconds(60)) ? \Carbon\Carbon::parse($sub->updated_at) : null; @endphp

                {{-- Desktop: 1 dòng ngang --}}
                <div class="hidden md:flex items-center gap-2">
                    <img src="{{ $avatarUrl }}" class="avatar w-6 h-6" alt="">
                    <a href="{{ route('profile', $sub->username ?? $sub->user_id) }}" style="font-size:0.8rem; font-weight:600; color:#1A1A1A;">{{ $sub->name }}</a>
                    <span style="font-size:0.7rem; color:#d17856; background:#E8F5E9; padding:0.1rem 0.375rem; border-radius:999px;">Ngày {{ $sub->day_number }}</span>
                    @if($sub->is_late)
                    <span style="font-size:0.6rem; color:#D97706; background:#FEF3C7; padding:0.1rem 0.375rem; border-radius:999px;">Trễ</span>
                    @endif
                    @if($sub->review_status === 'approved')
                    <span style="font-size:0.6rem; color:#059669; background:#D1FAE5; padding:0.1rem 0.375rem; border-radius:999px;">✓ Đã duyệt</span>
                    @elseif($sub->review_status === 'rejected')
                    <span style="font-size:0.6rem; color:#DC2626; background:#FEE2E2; padding:0.1rem 0.375rem; border-radius:999px;">✗ Từ chối</span>
                    @else
                    <span style="font-size:0.6rem; color:#92400E; background:#FEF3C7; padding:0.1rem 0.375rem; border-radius:999px;">Chờ duyệt</span>
                    @endif
                    <span style="font-size:0.65rem; color:#5C5C66;">{{ \Carbon\Carbon::parse($sub->created_at)->locale('vi')->diffForHumans() }}@if($subResubmittedAt) · <span style="color:#D97706;">nộp lại {{ $subResubmittedAt->locale('vi')->diffForHumans() }}</span>@endif</span>
                </div>

                {{-- Mobile: 2 dòng --}}
                <div class="md:hidden">
                    <div class="flex items-center gap-2" style="flex-wrap:wrap;">
                        <img src="{{ $avatarUrl }}" class="avatar w-6 h-6" alt="">
                        <a href="{{ route('profile', $sub->username ?? $sub->user_id) }}" style="font-size:0.8rem; font-weight:600; color:#1A1A1A;">{{ $sub->name }}</a>
                        <span style="font-size:0.7rem; color:#d17856; background:#E8F5E9; padding:0.1rem 0.375rem; border-radius:999px;">Ngày {{ $sub->day_number }}</span>
                        @if($sub->is_late)
                        <span style="font-size:0.6rem; color:#D97706; background:#FEF3C7; padding:0.1rem 0.375rem; border-radius:999px;">Trễ</span>
                        @endif
                        @if($sub->review_status === 'approved')
                        <span style="font-size:0.6rem; color:#059669; background:#D1FAE5; padding:0.1rem 0.375rem; border-radius:999px;">✓ Đã duyệt</span>
                        @elseif($sub->review_status === 'rejected')
                        <span style="font-size:0.6rem; color:#DC2626; background:#FEE2E2; padding:0.1rem 0.375rem; border-radius:999px;">✗ Từ chối</span>
                        @else
                        <span style="font-size:0.6rem; color:#92400E; background:#FEF3C7; padding:0.1rem 0.375rem; border-radius:999px;">Chờ duyệt</span>
                        @endif
                    </div>
                    <div style="padding-left:2rem; margin-top:0.2rem;">
                        <span style="font-size:0.65rem; color:#5C5C66;">{{ \Carbon\Carbon::parse($sub->created_at)->locale('vi')->diffForHumans() }}@if($subResubmittedAt) · <span style="color:#D97706;">nộp lại {{ $subResubmittedAt->locale('vi')->diffForHumans() }}</span>@endif</span>
                    </div>
                </div>
            </div>
            <p style="font-size:0.775rem; font-weight:500; color:#1A1A1A; padding-left:2rem;">{{ $sub->task_title }}</p>
            @if($sub->evidence)
            <p style="font-size:0.75rem; color:#5C5C66; padding-left:2rem; margin-top:0.25rem; line-height:1.4; overflow-wrap:break-word;">{!! preg_replace('#(https?://(?:(?!&quot;|&lt;|&gt;)[^\s<])+)#i', '<a href="$1" target="_blank" rel="noopener" style="color:#d17856; text-decoration:underline;">$1</a>', e($sub->evidence)) !!}</p>
            @endif
            @php $history = $reviewHistory[$sub->completion_id] ?? collect(); @endphp
            @if($history->count() > 0)
            <div style="padding-left:2rem; margin-top:0.375rem; border-left:2px solid #E1E1E1; margin-left:2rem;">
                <p style="font-size:0.65rem; font-weight:600; color:#5C5C66; margin-bottom:0.25rem;">Lịch sử review</p>
                @foreach($history as $rev)
                <div style="font-size:0.7rem; color:#2E2E2E; margin-bottom:0.2rem; line-height:1.4;">
                    <span style="color:{{ $rev->status === 'approved' ? '#059669' : '#DC2626' }}; font-weight:600;">
                        {{ $rev->status === 'approved' ? '✓ Duyệt' : '✗ Từ chối' }}
                    </span>
                    <span style="color:#5C5C66;">· {{ \Carbon\Carbon::parse($rev->created_at)->timezone('Asia/Ho_Chi_Minh')->locale('vi')->diffForHumans() }} · {{ \Carbon\Carbon::parse($rev->created_at)->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') }}</span>
                    @if($rev->note)
                    <div style="color:#DC2626; margin-top:0.1rem;">💬 {{ $rev->note }}</div>
                    @endif
                </div>
                @endforeach
            </div>
            @elseif($sub->review_note)
            <p style="font-size:0.7rem; color:#DC2626; padding-left:2rem; margin-top:0.25rem;">💬 {{ $sub->review_note }}</p>
            @endif

            {{-- Vote buttons: 'Vote hay' (good) + 'Vote xuất sắc' (excellent), admin only clickable --}}
            @php
                $isAdmin = auth()->check() && auth()->user()->is_admin;
                $votedGood      = in_array($sub->completion_id, $myVotedIds['good'] ?? []);
                $votedExcellent = in_array($sub->completion_id, $myVotedIds['excellent'] ?? []);
                $goodCount      = (int) ($sub->good_vote_count ?? 0);
                $excellentCount = (int) ($sub->excellent_vote_count ?? 0);
                $showVoteRow    = $isAdmin || $goodCount > 0 || $excellentCount > 0;
            @endphp
            @if($showVoteRow)
            <div style="padding-left:2rem; margin-top:0.375rem; display:flex; gap:0.375rem; flex-wrap:wrap;">
                {{-- Vote hay (good) --}}
                @if($isAdmin)
                <button wire:click="toggleVote({{ $sub->completion_id }}, 'good')" style="font-size:0.75rem; cursor:pointer; padding:0.2rem 0.5rem; border-radius:999px; border:1px solid {{ $votedGood ? '#d17856' : '#E1E1E1' }}; background:{{ $votedGood ? '#E8F5E9' : '#FFF' }}; color:{{ $votedGood ? '#d17856' : '#5C5C66' }}; transition:background-color .15s ease,border-color .15s ease,color .15s ease,box-shadow .15s ease;">
                    ▲ {{ $goodCount > 0 ? $goodCount : '' }} {{ $votedGood ? 'Đã vote hay' : 'Vote hay' }}
                </button>
                @elseif($goodCount > 0)
                <span style="font-size:0.75rem; padding:0.2rem 0.5rem; border-radius:999px; border:1px solid #d17856; background:#E8F5E9; color:#d17856;">
                    ▲ {{ $goodCount }} Vote hay
                </span>
                @endif

                {{-- Vote xuất sắc (excellent) --}}
                @if($isAdmin)
                <button wire:click="toggleVote({{ $sub->completion_id }}, 'excellent')" style="font-size:0.75rem; cursor:pointer; padding:0.2rem 0.5rem; border-radius:999px; border:1px solid {{ $votedExcellent ? '#C2410C' : '#E1E1E1' }}; background:{{ $votedExcellent ? '#FEF3C7' : '#FFF' }}; color:{{ $votedExcellent ? '#C2410C' : '#5C5C66' }}; transition:background-color .15s ease,border-color .15s ease,color .15s ease,box-shadow .15s ease;">
                    ★ {{ $excellentCount > 0 ? $excellentCount : '' }} {{ $votedExcellent ? 'Đã vote xuất sắc' : 'Vote xuất sắc' }}
                </button>
                @elseif($excellentCount > 0)
                <span style="font-size:0.75rem; padding:0.2rem 0.5rem; border-radius:999px; border:1px solid #C2410C; background:#FEF3C7; color:#C2410C;">
                    ★ {{ $excellentCount }} Xuất sắc
                </span>
                @endif
            </div>
            @endif

            {{-- Admin review buttons --}}
                @can('community-admin')
            @if($sub->review_status === 'pending')
            <div x-data="{ showReject: false, note: '' }" style="padding-left:2rem; margin-top:0.375rem;">
                @php
                    $reviewTask = $tasks->firstWhere('day_number', $sub->day_number);
                    $reviewPayload = is_array($reviewTask?->instruction_payload) ? $reviewTask->instruction_payload : [];
                @endphp
                <div style="display:flex; align-items:center; gap:.45rem; flex-wrap:wrap; margin-bottom:.4rem;">
                    <label style="font-size:.7rem; color:#456477;">Điểm <input type="number" min="0" max="100" wire:model="reviewScores.{{ $sub->completion_id }}" class="input" style="width:72px; padding:.2rem .35rem; font-size:.72rem;" placeholder="70"></label>
                    @if(!empty($reviewPayload['milestone_rubric']))
                    <details style="flex-basis:100%;">
                        <summary style="color:#125A96; cursor:pointer; font-size:.7rem; font-weight:700;">Mở rubric chấm nhanh</summary>
                        <div style="display:flex; flex-direction:column; gap:.25rem; margin:.35rem 0 .5rem; padding:.5rem; border:1px solid #D7E5EE; border-radius:6px; background:#F7FBFE;">
                            @foreach($reviewPayload['milestone_rubric'] as $criterion)
                            <label style="display:flex; align-items:flex-start; gap:.35rem; color:#456477; font-size:.68rem;"><input type="checkbox" wire:model="reviewRubrics.{{ $sub->completion_id }}.{{ $criterion['key'] }}"> <span>{{ $criterion['label'] }} ({{ $criterion['points'] }}đ)</span></label>
                            @endforeach
                        </div>
                    </details>
                    @endif
                </div>
                <div class="flex gap-1">
                    <button wire:click="approveSubmission({{ $sub->completion_id }})" class="btn btn-primary" style="font-size:0.7rem; padding:0.2rem 0.5rem;">✓ Duyệt</button>
                    <button @click="showReject = !showReject" class="btn btn-ghost" style="font-size:0.7rem; padding:0.2rem 0.5rem; color:#991B1B;">✗ Từ chối</button>
                </div>
                <div x-show="showReject" x-transition style="margin-top:0.375rem;">
                    <input x-model="note" type="text" class="input" placeholder="Lý do từ chối..." style="font-size:0.75rem; margin-bottom:0.25rem;">
                    <button @click="$wire.rejectSubmission({{ $sub->completion_id }}, note); showReject = false" class="btn btn-danger" style="font-size:0.7rem; padding:0.2rem 0.5rem;">Xác nhận từ chối</button>
                </div>
            </div>
            @endif
            @endcan
        </div>
        @endforeach
        @if($submissions->lastPage() > 1)
        <div class="flex items-center justify-between mt-3" style="font-size:0.75rem;">
            <span style="color:#5C5C66;">Trang {{ $submissions->currentPage() }}/{{ $submissions->lastPage() }}</span>
            <div class="flex gap-1">
                <button wire:click="$set('submissionPage', {{ max(1, $submissions->currentPage() - 1) }})" class="btn btn-ghost" style="font-size:0.65rem; padding:0.2rem 0.5rem;" @if($submissions->onFirstPage()) disabled style="opacity:0.4; font-size:0.65rem; padding:0.2rem 0.5rem;" @endif>← Trước</button>
                <button wire:click="$set('submissionPage', {{ min($submissions->lastPage(), $submissions->currentPage() + 1) }})" class="btn btn-ghost" style="font-size:0.65rem; padding:0.2rem 0.5rem;" @if(!$submissions->hasMorePages()) disabled style="opacity:0.4; font-size:0.65rem; padding:0.2rem 0.5rem;" @endif>Sau →</button>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>

@push('scripts')
<style>
    @keyframes resubmitPulse { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: .4; transform: scale(1.3); } }
    .resubmit-pulse-dot {
        display: inline-block; width: 8px; height: 8px;
        background: #059669; border-radius: 50%;
        animation: resubmitPulse 1.4s ease-in-out infinite;
    }
    [x-cloak] { display: none !important; }

    .resubmit-modal-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        width: 100vw; height: 100vh;
        background: rgba(0, 0, 0, 0.6);
        z-index: 9998;
        display: grid;
        place-items: center;
        padding: 1rem;
        overflow-y: auto;
    }
    .resubmit-modal-card {
        background: #fff;
        border-radius: 1rem;
        max-width: 400px;
        width: 100%;
        padding: 1.25rem;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        position: relative;
        margin: auto;
    }
</style>
@endpush
