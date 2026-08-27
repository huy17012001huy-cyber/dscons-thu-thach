<div class="membership-page" @if($selectedPlan || $selectedCommunityPlanId) wire:poll.5s @endif>
    <style>
        .membership-page { width: min(100%, 1040px); margin: 0 auto; padding: 30px clamp(16px, 3vw, 32px) 64px; color: #15384f; }
        .membership-hero { position: relative; overflow: hidden; padding: clamp(25px, 4vw, 42px); border: 1px solid #b9d7e6; border-radius: 22px; background: #eaf6fb; box-shadow: 0 13px 30px rgba(18, 59, 89, .08); }
        .membership-hero::after { position: absolute; top: -94px; right: 7%; width: 230px; height: 230px; border: 25px solid rgba(31, 119, 190, .11); border-radius: 50%; content: ''; pointer-events: none; }
        .membership-kicker { display: inline-flex; align-items: center; gap: 7px; margin: 0 0 11px; color: #125a96; font-size: 11px; font-weight: 850; letter-spacing: .11em; text-transform: uppercase; }
        .membership-kicker svg { color: #f39402; }
        .membership-hero h1 { position: relative; max-width: 650px; margin: 0; color: #123b59; font-size: clamp(28px, 4vw, 42px); font-weight: 850; letter-spacing: -.045em; line-height: 1.08; }
        .membership-hero p { position: relative; max-width: 640px; margin: 12px 0 0; color: #426477; font-size: 15px; line-height: 1.65; }
        .membership-clarity { position: relative; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1px; margin-top: 24px; border: 1px solid #c7deea; border-radius: 14px; overflow: hidden; background: #c7deea; }
        .membership-clarity-item { min-height: 82px; padding: 14px 15px; background: rgba(255, 255, 255, .8); }
        .membership-clarity-item svg { margin-bottom: 7px; }
        .membership-clarity-item strong { display: block; color: #123b59; font-size: 12px; font-weight: 850; }
        .membership-clarity-item span { display: block; margin-top: 3px; color: #5c7888; font-size: 11px; line-height: 1.42; }
        .membership-current { display: flex; align-items: center; gap: 12px; margin-top: 18px; padding: 14px 16px; border: 1px solid #b7ddc8; border-radius: 14px; background: #f0fbf4; }
        .membership-current svg { flex: 0 0 auto; }
        .membership-current strong { display: block; color: #17633f; font-size: 14px; }
        .membership-current span { display: block; margin-top: 3px; color: #3f7657; font-size: 12px; }
        .membership-section { margin-top: 28px; }
        .membership-section-heading { display: flex; align-items: end; justify-content: space-between; gap: 16px; margin-bottom: 13px; }
        .membership-section-heading h2 { margin: 0; color: #123b59; font-size: 21px; font-weight: 850; letter-spacing: -.025em; }
        .membership-section-heading p { max-width: 550px; margin: 5px 0 0; color: #597889; font-size: 13px; line-height: 1.55; }
        .membership-section-badge { flex: 0 0 auto; padding: 6px 9px; border: 1px solid #c5dfe9; border-radius: 999px; background: #f2f9fc; color: #125a96; font-size: 11px; font-weight: 800; }
        .membership-plan-grid { display: grid; grid-template-columns: minmax(250px, .72fr) minmax(0, 1.28fr); gap: 14px; align-items: stretch; }
        .membership-base-plan { display: flex; flex-direction: column; padding: 23px; border: 1px solid #d6e5eb; border-radius: 18px; background: #f8fbfc; }
        .membership-plan-eyebrow { display: inline-flex; width: fit-content; align-items: center; gap: 6px; padding: 5px 8px; border-radius: 999px; background: #e8f1f5; color: #526f80; font-size: 10px; font-weight: 850; letter-spacing: .06em; text-transform: uppercase; }
        .membership-base-plan h3, .membership-premium-plan h3 { margin: 14px 0 0; color: #123b59; font-size: 21px; font-weight: 850; letter-spacing: -.025em; }
        .membership-base-plan > p { margin: 7px 0 0; color: #5d7887; font-size: 13px; line-height: 1.55; }
        .membership-base-price { margin-top: auto; padding-top: 20px; color: #3f6173; font-size: 18px; font-weight: 850; }
        .membership-base-price span { display: block; margin-top: 3px; color: #6b8492; font-size: 11px; font-weight: 600; }
        .membership-includes { display: grid; gap: 9px; margin: 18px 0 0; padding: 0; list-style: none; }
        .membership-includes li { display: flex; align-items: flex-start; gap: 8px; color: #365568; font-size: 12px; line-height: 1.45; }
        .membership-includes li svg { flex: 0 0 auto; margin-top: 1px; }
        .membership-premium-plans { display: grid; gap: 12px; }
        .membership-premium-plan { position: relative; display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 14px; min-width: 0; padding: 22px; border: 1px solid #a9ccdf; border-radius: 18px; background: #fff; text-align: left; transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease, background-color .16s ease; }
        button.membership-premium-plan { width: 100%; cursor: pointer; font: inherit; }
        button.membership-premium-plan:hover { border-color: #1f77be; box-shadow: 0 9px 20px rgba(31,119,190,.12); transform: translateY(-2px); }
        button.membership-premium-plan:focus-visible { outline: 3px solid rgba(243,148,2,.35); outline-offset: 3px; }
        .membership-premium-plan.is-selected { border: 2px solid #1f77be; background: #f7fcfe; box-shadow: 0 10px 22px rgba(31,119,190,.13); }
        .membership-premium-plan.is-selected::after { position: absolute; top: 14px; right: 14px; width: 8px; height: 8px; border-radius: 50%; background: #1f77be; box-shadow: 0 0 0 4px #dceef6; content: ''; }
        .membership-premium-plan h3 { margin-top: 11px; }
        .membership-premium-plan p { max-width: 500px; margin: 6px 0 0; color: #557483; font-size: 13px; line-height: 1.5; }
        .membership-price { align-self: center; color: #125a96; font-size: 24px; font-weight: 900; letter-spacing: -.035em; text-align: right; white-space: nowrap; }
        .membership-price span { display: block; margin-top: 4px; color: #647f8e; font-size: 11px; font-weight: 700; letter-spacing: 0; }
        .membership-plan-action { display: inline-flex; width: fit-content; min-height: 38px; align-items: center; gap: 6px; margin-top: 16px; padding: 0 12px; border: 1px solid #1f77be; border-radius: 10px; background: #1f77be; color: #fff; font-size: 12px; font-weight: 800; }
        .membership-plan-action svg { color: #fff; }
        .membership-empty { padding: 25px; border: 1px dashed #abc9d8; border-radius: 16px; background: #f8fbfc; color: #557483; font-size: 13px; line-height: 1.6; }
        .membership-payment { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 20px; align-items: center; margin-top: 16px; padding: 21px; border: 1px solid #90c0d8; border-left: 5px solid #f39402; border-radius: 14px; background: #fff; box-shadow: 0 8px 18px rgba(18,59,89,.07); }
        .membership-payment h2 { margin: 0; color: #123b59; font-size: 17px; font-weight: 850; letter-spacing: -.02em; }
        .membership-payment > div > p { margin: 5px 0 0; color: #5c7787; font-size: 12px; line-height: 1.5; }
        .membership-payment-data { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; }
        .membership-payment-data div { min-width: 145px; padding: 10px 11px; border: 1px solid #e0ebf0; border-radius: 10px; background: #f8fbfc; }
        .membership-payment-data span { display: block; color: #718996; font-size: 10px; font-weight: 700; }
        .membership-payment-data strong { display: block; margin-top: 4px; color: #125a96; font-size: 14px; font-weight: 850; }
        .membership-payment-qr { width: 132px; height: 132px; padding: 7px; border: 1px solid #c9dfe9; border-radius: 12px; background: #fff; box-shadow: 0 4px 12px rgba(18,59,89,.08); }
        .membership-payment-no-qr { margin-top: 12px !important; color: #9a5b00 !important; }
        @media (max-width: 720px) { .membership-clarity, .membership-plan-grid, .membership-payment { grid-template-columns: 1fr; } .membership-payment-qr { order: -1; } .membership-section-heading { align-items: flex-start; flex-direction: column; gap: 8px; } }
        @media (max-width: 480px) { .membership-page { padding: 20px 14px 48px; } .membership-hero { border-radius: 18px; } .membership-clarity { grid-template-columns: 1fr; } .membership-premium-plan { grid-template-columns: 1fr; } .membership-price { align-self: start; text-align: left; } }
        @media (prefers-reduced-motion: reduce) { .membership-premium-plan { transition: none; } }
    </style>

    <header class="membership-hero">
        <div class="membership-kicker"><x-icon name="shield" size="15" /> {{ brand()->name }}</div>
        <h1>Gói thành viên, nói rõ quyền lợi.</h1>
        <p>Bạn đã là thành viên của community thì vẫn có thể đọc, đăng bài và tương tác. Membership Premium chỉ mở quyền <strong>mua</strong> mọi challenge và khóa học trong community này.</p>
        <div class="membership-clarity" aria-label="Membership hoạt động như thế nào">
            <div class="membership-clarity-item"><x-icon name="users" size="18" /><strong>Tham gia community</strong><span>Không cần mua Membership để đăng bài và tương tác.</span></div>
            <div class="membership-clarity-item"><x-icon name="graduation" size="18" /><strong>Học theo lựa chọn</strong><span>Không có Membership vẫn mua lẻ khóa học hoặc challenge được.</span></div>
            <div class="membership-clarity-item"><x-icon name="shopping-cart" size="18" /><strong>Marketplace riêng</strong><span>Các sản phẩm khác trong Marketplace luôn mua theo từng sản phẩm.</span></div>
        </div>
    </header>

    @if($membership && $membership->isPremium() && $membership->isActive())
        <section class="membership-current" aria-live="polite">
            <x-icon name="check-circle" size="24" />
            <div><strong>{{ $membershipLabel }} của bạn đang hoạt động</strong><span>Hết hạn: {{ $membership->expires_at?->format('d/m/Y') ?? 'Không giới hạn' }}</span></div>
        </section>
    @endif

    <section class="membership-section" aria-labelledby="membership-plans-title">
        <div class="membership-section-heading">
            <div><h2 id="membership-plans-title">Chọn quyền mua nội dung học tập</h2><p>Gói áp dụng riêng cho {{ brand()->name }}. Gói nào cũng không tự bao gồm học phí của khóa học hoặc challenge.</p></div>
            <span class="membership-section-badge">{{ brand()->name }}</span>
        </div>

        <div class="membership-plan-grid">
            <article class="membership-base-plan">
                <span class="membership-plan-eyebrow"><x-icon name="users" size="13" /> Mặc định</span>
                <h3>Thành viên community</h3>
                <p>Trạng thái có sẵn sau khi bạn tham gia {{ brand()->name }}.</p>
                <ul class="membership-includes">
                    <li><x-icon name="check" size="16" /> Đọc nội dung, đăng bài và bình luận.</li>
                    <li><x-icon name="check" size="16" /> Mua lẻ khóa học hoặc challenge khi cần.</li>
                    <li><x-icon name="check" size="16" /> Mua các sản phẩm Marketplace theo giá từng sản phẩm.</li>
                </ul>
                <div class="membership-base-price">Không cần mua<span>Không phải một gói thanh toán.</span></div>
            </article>

            <div class="membership-premium-plans">
                @forelse($premiumPlans as $communityPlan)
                    <button type="button" wire:click="selectCommunityPlan({{ $communityPlan->id }})" class="membership-premium-plan {{ $selectedCommunityPlan?->id === $communityPlan->id ? 'is-selected' : '' }}" aria-pressed="{{ $selectedCommunityPlan?->id === $communityPlan->id ? 'true' : 'false' }}">
                        <div>
                            <span class="membership-plan-eyebrow"><x-icon name="shield" size="13" /> Membership Premium</span>
                            <h3>{{ $communityPlan->name }}</h3>
                            <p>Mở quyền mua mọi challenge và khóa học trong {{ brand()->name }}.</p>
                            <span class="membership-plan-action">{{ $selectedCommunityPlan?->id === $communityPlan->id ? 'Đã chọn gói' : 'Chọn gói này' }} <x-icon name="arrow-right" size="15" /></span>
                        </div>
                        <div class="membership-price">{{ number_format($communityPlan->price, 0, ',', '.') }}đ<span>{{ $communityPlan->duration_days ? $communityPlan->duration_days.' ngày' : 'Không giới hạn thời hạn' }}</span></div>
                    </button>
                @empty
                    <div class="membership-empty">Community này chưa mở bán Membership Premium. Bạn vẫn có thể tham gia community và mua lẻ nội dung phù hợp.</div>
                @endforelse
            </div>
        </div>

        @if($selectedCommunityPlan)
            @php
                $communityTransferCode = 'MC'.brand()->id.'P'.$selectedCommunityPlan->id.'U'.auth()->id();
                $bankAccount = $selectedCommunityPlan->sepay_account ?: config('services.sepay.bank_account');
                $bankName = $selectedCommunityPlan->sepay_bank ?: config('services.sepay.bank_name');
                $communityQr = $bankAccount ? 'https://qr.sepay.vn/img?'.http_build_query(['acc' => $bankAccount, 'bank' => $bankName, 'amount' => $selectedCommunityPlan->price, 'des' => $communityTransferCode, 'template' => 'compact']) : null;
            @endphp
            <section class="membership-payment" aria-labelledby="membership-payment-title">
                <div>
                    <h2 id="membership-payment-title">Thanh toán {{ $selectedCommunityPlan->name }}</h2>
                    <p>Chuyển đúng số tiền và nội dung dưới đây. Hệ thống sẽ kích hoạt quyền mua sau khi nhận được giao dịch.</p>
                    <div class="membership-payment-data">
                        <div><span>Nội dung chuyển khoản</span><strong>{{ $communityTransferCode }}</strong></div>
                        <div><span>Số tiền</span><strong>{{ number_format($selectedCommunityPlan->price, 0, ',', '.') }}đ</strong></div>
                    </div>
                    @unless($communityQr)<p class="membership-payment-no-qr">Gói này chưa có thông tin thanh toán. Vui lòng liên hệ quản trị viên community.</p>@endunless
                </div>
                @if($communityQr)<img class="membership-payment-qr" src="{{ $communityQr }}" alt="Mã QR thanh toán {{ $selectedCommunityPlan->name }}" width="132" height="132">@endif
            </section>
        @endif
    </section>
</div>
