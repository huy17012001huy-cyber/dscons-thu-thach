<div class="account-menu-inner">
    <div class="account-menu-head">
        <img src="{{ auth()->user()->avatar_url }}" alt="">
        <div>
            <strong>{{ auth()->user()->name }}</strong>
            <span>{{ '@'.(auth()->user()->username ?: 'thanh-vien') }}</span>
        </div>
    </div>
    <div class="account-membership">
        <x-icon name="award" size="15" />
        <span>{{ $__accountMembership?->plan ?: ($__accountMembership?->tier ? ucfirst($__accountMembership->tier) : 'Chưa có gói') }}</span>
        @if($__accountMembership?->isActive() || $__accountMembership?->isTrial())<small>Đang dùng</small>@endif
    </div>
    <div class="account-menu-links">
        <a href="{{ route('profile', auth()->user()->username ?: auth()->id()) }}" @click="accountOpen=false">
            <x-icon name="user" size="19" /> <span>Hồ sơ của bạn</span>
        </a>
        <a href="{{ route('account.settings') }}" @click="accountOpen=false">
            <x-icon name="settings" size="19" /> <span>Cài đặt tài khoản</span>
        </a>
        <a href="{{ route('account.revit-device') }}" @click="accountOpen=false">
            <x-icon name="layers" size="19" /> <span>Thiết bị Revit</span>
        </a>
    </div>
    <form method="POST" action="{{ route('logout') }}" class="account-logout-form">
        @csrf
        <button type="submit"><x-icon name="logout" size="19" /> <span>Đăng xuất</span></button>
    </form>
</div>
