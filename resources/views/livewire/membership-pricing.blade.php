<div @if($selectedPlan) wire:poll.5s @endif>
    <div style="max-width:720px; margin:0 auto;">
        <div class="text-center mb-6">
            <h1 style="font-size:1.5rem; font-weight:800; color:#1A1A1A;">Gói thành viên</h1>
            <p style="font-size:0.875rem; color:#5C5C66; margin-top:0.375rem;">Tham gia cộng đồng Website Thử Thách để tiếp cận kiến thức, challenge và networking</p>
        </div>

        {{-- Current membership status --}}
        @if($membership && $membership->isActive())
        <div class="card mb-4" style="background:#D1FAE5; border:1px solid #A7F3D0;">
            <div class="flex items-center gap-2">
                <span style="font-size:1.25rem;">✓</span>
                <div>
                    <p style="font-size:0.85rem; font-weight:700; color:#065F46;">Membership đang hoạt động</p>
                    <p style="font-size:0.75rem; color:#065F46;">Hết hạn: {{ $membership->expires_at?->format('d/m/Y') ?? 'Không giới hạn' }}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Pricing cards --}}
        <div class="grid gap-3" style="grid-template-columns: repeat(auto-fit, minmax(155px, 1fr));">
            @foreach($plans as $weeks => $plan)
            @php
                $total = $plan['weeks'] * $plan['price_per_week'];
                $isPopular = $weeks === 52;
                $isSelected = $selectedPlan === $weeks;
            @endphp
            <button wire:click="selectPlan({{ $weeks }})"
                class="card text-center" style="padding:1.25rem 0.75rem; cursor:pointer; transition:all 0.15s; position:relative;
                    {{ $isSelected ? 'border:2px solid #d17856; background:#F0FDF4;' : 'border:1px solid #E1E1E1;' }}
                    {{ $isPopular ? 'border-color:#d17856;' : '' }}">
                @if($isPopular)
                <div style="position:absolute; top:-10px; left:50%; transform:translateX(-50%); background:#d17856; color:#FFF; font-size:0.6rem; font-weight:700; padding:0.15rem 0.5rem; border-radius:999px; white-space:nowrap;">HOT</div>
                @endif
                @if($plan['save'] > 0)
                <div style="position:absolute; top:-10px; right:8px; background:#DC2626; color:#FFF; font-size:0.6rem; font-weight:700; padding:0.15rem 0.375rem; border-radius:999px;">-{{ $plan['save'] }}%</div>
                @endif
                <p style="font-size:0.8rem; font-weight:700; color:#1A1A1A; margin-bottom:0.5rem;">{{ $plan['label'] }}</p>
                <p style="font-size:1.25rem; font-weight:800; color:#d17856;">{{ number_format($plan['price_per_week'], 0, ',', '.') }}đ</p>
                <p style="font-size:0.65rem; color:#5C5C66;">/tuần</p>
                <div style="height:1px; background:#E1E1E1; margin:0.75rem 0;"></div>
                <p style="font-size:0.85rem; font-weight:700; color:#1A1A1A;">{{ number_format($total, 0, ',', '.') }}đ</p>
                <p style="font-size:0.65rem; color:#5C5C66;">tổng</p>
            </button>
            @endforeach
        </div>

        {{-- Payment info --}}
        @if($selectedPlan)
        @php
            $plan = $plans[$selectedPlan];
            $total = $plan['weeks'] * $plan['price_per_week'];
        @endphp
        <div class="card mt-4" style="border-left:3px solid #d17856;">
            <h2 style="font-size:0.9rem; font-weight:700; color:#1B5E20; margin-bottom:0.75rem;">▣ Thanh toán — {{ $plan['label'] }}</h2>

            <div class="flex flex-wrap gap-4 mb-4">
                <div>
                    <p style="font-size:0.7rem; color:#5C5C66;">Giá/tuần</p>
                    <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">{{ number_format($plan['price_per_week'], 0, ',', '.') }}đ</p>
                </div>
                <div>
                    <p style="font-size:0.7rem; color:#5C5C66;">Thời gian</p>
                    <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">{{ $plan['weeks'] }} tuần</p>
                </div>
                <div>
                    <p style="font-size:0.7rem; color:#5C5C66;">Tổng thanh toán</p>
                    <p style="font-size:1.1rem; font-weight:800; color:#d17856;">{{ number_format($total, 0, ',', '.') }}đ</p>
                </div>
            </div>

            @php
                $transferCode = 'MEM' . $plan['weeks'] . 'WU' . auth()->id();
                $bankAccount = config('services.sepay.bank_account');
                $bankName = config('services.sepay.bank_name');
                $qrUrl = $bankAccount
                    ? 'https://qr.sepay.vn/img?' . http_build_query(['acc' => $bankAccount, 'bank' => $bankName, 'amount' => $total, 'des' => $transferCode, 'template' => 'compact'])
                    : null;
            @endphp

            <div style="background:#F7F5F3; border-radius:0.5rem; padding:0.75rem; margin-bottom:0.75rem;">
                <p style="font-size:0.8rem; font-weight:600; color:#1A1A1A; margin-bottom:0.5rem;">Chuyển khoản qua SePay</p>

                @if($qrUrl)
                <div class="text-center" style="margin-bottom:0.75rem;">
                    <img src="{{ $qrUrl }}" alt="QR Thanh toán" style="max-width:220px; margin:0 auto; border-radius:0.5rem;">
                    <p style="font-size:0.7rem; color:#5C5C66; margin-top:0.375rem;">Quét mã QR bằng app ngân hàng</p>
                </div>
                @endif

                <div style="background:#FFFFFF; border:1px solid #E1E1E1; border-radius:0.375rem; padding:0.625rem; margin-bottom:0.375rem;">
                    <div class="flex justify-between items-center" style="margin-bottom:0.25rem;">
                        <span style="font-size:0.75rem; color:#5C5C66;">Nội dung CK:</span>
                        <strong style="font-size:0.85rem; color:#d17856; letter-spacing:0.05em;">{{ $transferCode }}</strong>
                    </div>
                    <div class="flex justify-between items-center">
                        <span style="font-size:0.75rem; color:#5C5C66;">Số tiền:</span>
                        <strong style="font-size:0.85rem; color:#1A1A1A;">{{ number_format($total, 0, ',', '.') }}đ</strong>
                    </div>
                </div>

                <p style="font-size:0.7rem; color:#059669; font-weight:500;">⚡ Hệ thống tự kích hoạt sau khi nhận được tiền (1-3 phút)</p>
            </div>

            {{-- Benefits --}}
            <div style="margin-top:0.75rem;">
                <p style="font-size:0.8rem; font-weight:600; color:#1A1A1A; margin-bottom:0.5rem;">Quyền lợi thành viên:</p>
                <div class="flex flex-col gap-1">
                    @foreach(['Truy cập toàn bộ nội dung Feed, CỐT, Tín hiệu', 'Đăng bài, bình luận, tương tác', 'Tham gia Challenge & Khóa học', 'Nhắn tin trực tiếp (DM) với thành viên', 'Tích lũy XP, lên level, nhận badge', 'Hệ thống Affiliate kiếm thêm thu nhập'] as $benefit)
                    <div class="flex items-center gap-2">
                        <span style="color:#059669; font-size:0.75rem;">✓</span>
                        <span style="font-size:0.8rem; color:#2E2E2E;">{{ $benefit }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
