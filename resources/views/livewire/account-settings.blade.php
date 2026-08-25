<div class="account-settings-page">
    <style>
        .account-settings-page { width:min(100%, 920px); margin:0 auto; color:#123B59; }
        .account-settings-hero { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1rem; padding:1rem 1.1rem; border:1px solid #C9DFEA; border-radius:18px; background:#fff; box-shadow:0 6px 18px rgba(18,59,89,.05); }
        .account-settings-user { display:flex; align-items:center; gap:.75rem; min-width:0; }
        .account-settings-user img { width:58px; height:58px; border:3px solid #E1F4F7; border-radius:50%; object-fit:cover; }
        .account-settings-user strong { display:block; overflow:hidden; font-size:1rem; font-weight:850; text-overflow:ellipsis; white-space:nowrap; }
        .account-settings-user span { display:block; margin-top:.22rem; color:#456477; font-size:.74rem; }
        .account-settings-edit { display:inline-flex; min-height:40px; align-items:center; gap:.4rem; padding:0 .85rem; border:1px solid #1F77BE; border-radius:10px; color:#fff; background:#1F77BE; font-size:.74rem; font-weight:800; text-decoration:none; white-space:nowrap; }
        .account-settings-edit:hover { color:#fff; background:#125A96; }
        .account-settings-section { margin-top:1rem; padding:1.05rem 1.1rem; border:1px solid #D7E5EE; border-radius:18px; background:#fff; box-shadow:0 4px 14px rgba(18,59,89,.035); }
        .account-settings-section h2 { display:flex; align-items:center; gap:.45rem; margin:0; color:#123B59; font-size:1rem; font-weight:850; letter-spacing:-.02em; }
        .account-settings-section > p { margin:.35rem 0 0; color:#456477; font-size:.75rem; line-height:1.55; }
        .settings-community-list { display:grid; gap:.5rem; margin-top:.85rem; }
        .settings-community-row { display:flex; align-items:center; gap:.7rem; min-height:56px; padding:.55rem .65rem; border:1px solid #E7EEF1; border-radius:12px; background:#FBFDFE; }
        .settings-community-row img, .settings-community-fallback { width:34px; height:34px; flex:0 0 auto; border-radius:10px; object-fit:cover; }
        .settings-community-fallback { display:grid; place-items:center; color:#125A96; background:#E1F4F7; font-size:.8rem; font-weight:850; }
        .settings-community-info { min-width:0; flex:1; }
        .settings-community-info strong { display:block; overflow:hidden; color:#123B59; font-size:.8rem; font-weight:800; text-overflow:ellipsis; white-space:nowrap; }
        .settings-community-info span { display:block; margin-top:.15rem; color:#55758A; font-size:.7rem; }
        .settings-toggle { display:inline-flex; align-items:center; gap:.4rem; min-height:34px; padding:0 .65rem; border:1px solid #B8D7E6; border-radius:999px; color:#125A96; background:#EAF5FB; font-size:.68rem; font-weight:800; cursor:pointer; }
        .settings-toggle.is-off { color:#456477; border-color:#C9DFEA; background:#F5F8FA; }
        .settings-toggle-dot { width:8px; height:8px; border-radius:50%; background:#1F77BE; }
        .settings-toggle.is-off .settings-toggle-dot { background:#6B8494; }
        .settings-orders { display:grid; gap:.35rem; margin-top:.85rem; }
        .settings-order { display:flex; align-items:center; gap:.65rem; padding:.7rem .15rem; border-bottom:1px solid #E7EEF1; }
        .settings-order:last-child { border-bottom:0; }
        .settings-order-icon { display:grid; place-items:center; width:32px; height:32px; flex:0 0 auto; border-radius:10px; color:#125A96; background:#E1F4F7; }
        .settings-order-main { min-width:0; flex:1; }
        .settings-order-main strong { display:block; overflow:hidden; color:#123B59; font-size:.78rem; font-weight:800; text-overflow:ellipsis; white-space:nowrap; }
        .settings-order-main span { display:block; margin-top:.2rem; color:#55758A; font-size:.7rem; }
        .settings-order-amount { color:#F39402; font-size:.75rem; font-weight:850; white-space:nowrap; }
        .settings-empty { margin-top:.8rem; padding:1rem; border:1px dashed #B8D7E6; border-radius:12px; color:#456477; background:#F7FCFD; font-size:.75rem; text-align:center; }
        .settings-invoice-tabs { display:flex; gap:.35rem; margin-top:.85rem; padding:.25rem; border:1px solid #D7E5EE; border-radius:12px; background:#F7FBFD; }
        .settings-invoice-tab { flex:1; min-height:38px; border:1px solid transparent; border-radius:9px; color:#456477; background:transparent; font-size:.75rem; font-weight:800; cursor:pointer; }
        .settings-invoice-tab.is-active { border-color:#1F77BE; color:#125A96; background:#E1F4F7; }
        .settings-form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.75rem; margin-top:.85rem; }
        .settings-field { display:grid; gap:.3rem; }
        .settings-field.is-wide { grid-column:1/-1; }
        .settings-field label { color:#456477; font-size:.7rem; font-weight:800; }
        .settings-field input { width:100%; min-height:42px; padding:0 .7rem; border:1px solid #D7E5EE; border-radius:9px; color:#123B59; background:#fff; font:inherit; font-size:.76rem; }
        .settings-field input:focus { border-color:#1F77BE; outline:3px solid rgba(31,119,190,.14); }
        .settings-field input[readonly] { color:#456477; background:#F5F8FA; }
        .settings-field input::placeholder { color:#55758A; opacity:1; }
        .settings-error { color:#B42318; font-size:.68rem; }
        .settings-save { display:inline-flex; min-height:40px; align-items:center; justify-content:center; gap:.4rem; margin-top:.8rem; padding:0 .9rem; border:0; border-radius:10px; color:#fff; background:#1F77BE; font-size:.75rem; font-weight:850; cursor:pointer; }
        .settings-save:hover { background:#125A96; }
        .settings-account-actions { display:flex; gap:.6rem; margin-top:.85rem; }
        .settings-logout { display:inline-flex; min-height:40px; align-items:center; gap:.4rem; padding:0 .85rem; border:1px solid #F4C8C8; border-radius:10px; color:#C43D3D; background:#FFF0F0; font-size:.75rem; font-weight:850; cursor:pointer; }
        @media (max-width:640px) { .account-settings-hero { align-items:flex-start; flex-direction:column; }.account-settings-edit { width:100%; justify-content:center; }.settings-form-grid { grid-template-columns:1fr; }.settings-field.is-wide { grid-column:auto; }.settings-order { align-items:flex-start; }.settings-order-amount { margin-left:auto; }.account-settings-section { padding:.9rem; } }
    </style>

    <section class="account-settings-hero">
        <div class="account-settings-user">
            <img src="{{ $user->avatar_url }}" alt="Ảnh đại diện của {{ $user->name }}">
            <div><strong>{{ $user->name }}</strong><span>{{ '@'.($user->username ?: 'thanh-vien') }} · {{ $user->email }}</span></div>
        </div>
        <a class="account-settings-edit" href="{{ route('profile.edit') }}"><x-icon name="user" size="16" /> Sửa hồ sơ</a>
    </section>

    <section class="account-settings-section">
        <h2><x-icon name="bell" size="18" /> Thông báo</h2>
        <p>Tắt thông báo hoạt động trong từng community mà bạn đang tham gia.</p>
        <div class="settings-community-list">
            @forelse($communities as $community)
                @php($enabled = $notificationStates[$community->id] ?? true)
                <div class="settings-community-row">
                    @if($community->logo_path)<img src="{{ asset('storage/'.$community->logo_path) }}" alt="">@else<span class="settings-community-fallback">{{ strtoupper(substr($community->name, 0, 1)) }}</span>@endif
                    <div class="settings-community-info"><strong>{{ $community->name }}</strong><span>Thông báo bài viết, bình luận và hoạt động liên quan</span></div>
                    <button type="button" class="settings-toggle {{ $enabled ? '' : 'is-off' }}" wire:click="toggleNotifications({{ $community->id }})" aria-label="{{ $enabled ? 'Tắt' : 'Bật' }} thông báo {{ $community->name }}"><span class="settings-toggle-dot"></span>{{ $enabled ? 'Đang bật' : 'Đã tắt' }}</button>
                </div>
            @empty
                <div class="settings-empty">Bạn chưa tham gia community nào.</div>
            @endforelse
        </div>
    </section>

    <section class="account-settings-section">
        <h2><x-icon name="receipt" size="18" /> Lịch sử đã mua</h2>
        <p>Những gì bạn đã mua gồm sản phẩm, khóa học, Challenge, membership và gói tuyển dụng.</p>
        <div class="settings-orders">
            @forelse($orders as $order)
                @php($orderIcon = match($order['type']) { 'membership' => 'award', 'course' => 'graduation', 'challenge' => 'target', 'recruiter' => 'briefcase', default => 'layers' })
                <div class="settings-order">
                    <span class="settings-order-icon"><x-icon name="{{ $orderIcon }}" size="17" /></span>
                    <div class="settings-order-main"><strong>{{ $order['title'] }}</strong><span>{{ $order['community']->name }} · {{ $order['date']?->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y') }} · {{ $order['status'] === 'pending_payment' ? 'Chờ thanh toán' : ($order['status'] === 'active' ? 'Đang hoạt động' : ucfirst($order['status'])) }}@if($order['reference']) · {{ $order['reference'] }} @endif</span></div>
                    <span class="settings-order-amount">{{ $order['amount'] > 0 ? number_format($order['amount'], 0, ',', '.').'đ' : 'Miễn phí' }}</span>
                </div>
            @empty
                <div class="settings-empty">Chưa có lịch sử mua hàng.</div>
            @endforelse
        </div>
    </section>

    <section class="account-settings-section">
        <h2><x-icon name="file-text" size="18" /> Thông tin xuất hóa đơn</h2>
        <p>Lưu sẵn thông tin để điền nhanh khi mua hàng trong community.</p>
        <div class="settings-invoice-tabs" role="tablist" aria-label="Loại thông tin xuất hóa đơn">
            <button type="button" class="settings-invoice-tab {{ $invoiceType === 'personal' ? 'is-active' : '' }}" wire:click="setInvoiceType('personal')">Cá nhân</button>
            <button type="button" class="settings-invoice-tab {{ $invoiceType === 'company' ? 'is-active' : '' }}" wire:click="setInvoiceType('company')">Công ty</button>
        </div>
        <div class="settings-form-grid">
            @if($invoiceType === 'personal')
                <div class="settings-field"><label for="settings-full-name">Tên người mua</label><input id="settings-full-name" wire:model="fullName" type="text" autocomplete="name" placeholder="Họ và tên trên CCCD">@error('fullName')<span class="settings-error">{{ $message }}</span>@enderror</div>
                <div class="settings-field"><label for="settings-identity">CCCD</label><input id="settings-identity" wire:model="identityNumber" type="text" inputmode="numeric" placeholder="Số CCCD">@error('identityNumber')<span class="settings-error">{{ $message }}</span>@enderror</div>
            @else
                <div class="settings-field"><label for="settings-company">Tên công ty</label><input id="settings-company" wire:model="companyName" type="text" autocomplete="organization" placeholder="Tên công ty">@error('companyName')<span class="settings-error">{{ $message }}</span>@enderror</div>
                <div class="settings-field"><label for="settings-tax">Mã số thuế</label><input id="settings-tax" wire:model="taxCode" type="text" placeholder="Mã số thuế">@error('taxCode')<span class="settings-error">{{ $message }}</span>@enderror</div>
            @endif
            <div class="settings-field"><label for="settings-invoice-email">Email nhận hóa đơn</label><input id="settings-invoice-email" wire:model="invoiceEmail" type="email" autocomplete="email" placeholder="email@example.com">@error('invoiceEmail')<span class="settings-error">{{ $message }}</span>@enderror</div>
            <div class="settings-field"><label for="settings-phone">Số điện thoại</label><input id="settings-phone" wire:model="phone" type="tel" autocomplete="tel" placeholder="Số điện thoại">@error('phone')<span class="settings-error">{{ $message }}</span>@enderror</div>
            <div class="settings-field is-wide"><label for="settings-address">Địa chỉ</label><input id="settings-address" wire:model="address" type="text" autocomplete="street-address" placeholder="Địa chỉ nhận hóa đơn">@error('address')<span class="settings-error">{{ $message }}</span>@enderror</div>
        </div>
        <button type="button" class="settings-save" wire:click="saveBilling" wire:loading.attr="disabled"><x-icon name="check" size="16" /><span wire:loading.remove wire:target="saveBilling">Lưu thông tin</span><span wire:loading wire:target="saveBilling">Đang lưu...</span></button>
    </section>

    <section class="account-settings-section">
        <h2><x-icon name="settings" size="18" /> Tài khoản</h2>
        <p>Ngôn ngữ hiển thị: <strong>Tiếng Việt</strong></p>
        <div class="settings-account-actions"><form method="POST" action="{{ route('logout') }}">@csrf<button class="settings-logout" type="submit"><x-icon name="logout" size="16" /> Đăng xuất</button></form></div>
    </section>
</div>
