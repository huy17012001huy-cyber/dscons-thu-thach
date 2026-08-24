<div class="admin-orders-page">
    <style>
        .admin-orders-page { max-width: 1180px; margin: 0 auto; }
        .admin-orders-page .card { border: 1px solid #D7E5EA; border-radius: 16px; background: #fff; }
        .admin-orders-page .input:focus { border-color: #1F77BE; box-shadow: 0 0 0 3px rgba(31,119,190,.14); }
        .admin-orders-page .order-row { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 0; border-bottom:1px solid #E8F0F3; }
        .admin-orders-page .order-row:last-child { border-bottom:0; }
        @media (max-width:720px) { .admin-orders-page .order-row { align-items:flex-start; flex-direction:column; } }
    </style>

    <div class="flex items-center justify-between gap-3 mb-4">
        <div>
            <h1 style="font-size:1.25rem; font-weight:800; color:#123B59; margin:0;">Đơn hàng &amp; cấp quyền</h1>
            <p style="font-size:.78rem; color:#61798A; margin:.35rem 0 0;">Duyệt đơn chờ thanh toán hoặc tặng quyền học viên thủ công.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost" style="text-decoration:none; white-space:nowrap;">← Về Admin</a>
    </div>

    <div class="card" style="padding:1rem; margin-bottom:1rem; border-color:#B8D7E6; background:#F7FCFD;">
        <h2 style="font-size:.95rem; font-weight:800; color:#123B59; margin:0 0 .25rem;">Tặng quyền truy cập</h2>
        <p style="font-size:.72rem; color:#61798A; margin:0 0 .8rem;">Dùng cho học viên được tặng. Hệ thống ghi payment ref dạng GIFT-ADMIN và không tạo doanh thu giả.</p>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:.65rem;">
            <div>
                <label style="display:block; font-size:.72rem; font-weight:700; color:#456477; margin-bottom:.25rem;">Tìm học viên</label>
                <input wire:model.live.debounce.300ms="grantUserSearch" class="input" style="width:100%;" placeholder="Tên, email hoặc username">
                <select wire:model="grantUserId" class="input" style="width:100%; margin-top:.4rem;">
                    <option value="">— Chọn học viên —</option>
                    @foreach($grantUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} · {{ $user->email }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block; font-size:.72rem; font-weight:700; color:#456477; margin-bottom:.25rem;">Loại quyền</label>
                <select wire:model.live="grantType" class="input" style="width:100%;">
                    <option value="challenge">Challenge</option>
                    <option value="course">Khóa học</option>
                    <option value="product">Tài nguyên</option>
                </select>
            </div>
            <div>
                <label style="display:block; font-size:.72rem; font-weight:700; color:#456477; margin-bottom:.25rem;">Nội dung tặng</label>
                <select wire:model="grantResourceId" class="input" style="width:100%;">
                    <option value="">— Chọn nội dung —</option>
                    @foreach($grantResources as $resource)
                        <option value="{{ $resource->id }}">{{ $resource->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @error('grantUserId') <p style="color:#DC2626; font-size:.72rem; margin:.4rem 0 0;">{{ $message }}</p> @enderror
        @error('grantResourceId') <p style="color:#DC2626; font-size:.72rem; margin:.4rem 0 0;">{{ $message }}</p> @enderror
        <button wire:click="grantAccess" wire:loading.attr="disabled" class="btn btn-primary" style="margin-top:.75rem; font-size:.75rem;">🎁 Kích hoạt quyền tặng</button>
    </div>

    <div class="card" style="padding:1rem;">
        <div class="flex items-center justify-between gap-3" style="margin-bottom:.55rem;">
            <div>
                <h2 style="font-size:.95rem; font-weight:800; color:#123B59; margin:0;">Đơn đang chờ xử lý</h2>
                <p style="font-size:.72rem; color:#61798A; margin:.25rem 0 0;">Kích hoạt thủ công sẽ ghi người duyệt và mã ADMIN-ACTIVATE.</p>
            </div>
            <input wire:model.live.debounce.300ms="search" class="input" style="max-width:260px;" placeholder="Tìm tên hoặc email">
        </div>

        @if($pendingChallenges->isEmpty() && $pendingCourses->isEmpty() && $pendingProducts->isEmpty())
            <div style="padding:1.5rem 0; text-align:center; color:#61798A; font-size:.8rem;">Không có đơn đang chờ.</div>
        @endif

        @foreach($pendingChallenges as $member)
            <div class="order-row">
                <div>
                    <strong style="display:block; color:#123B59; font-size:.82rem;">Challenge · {{ $member->expedition->title }}</strong>
                    <span style="font-size:.72rem; color:#61798A;">{{ $member->user->name }} · {{ $member->user->email }} · {{ $member->status }}</span>
                </div>
                <button wire:click="activateOrder('challenge', {{ $member->id }})" class="btn btn-primary" style="font-size:.7rem;">Kích hoạt</button>
            </div>
        @endforeach

        @foreach($pendingCourses as $enrollment)
            <div class="order-row">
                <div>
                    <strong style="display:block; color:#123B59; font-size:.82rem;">Khóa học · {{ $enrollment->course->title }}</strong>
                    <span style="font-size:.72rem; color:#61798A;">{{ $enrollment->user->name }} · {{ $enrollment->user->email }}</span>
                </div>
                <button wire:click="activateOrder('course', {{ $enrollment->id }})" class="btn btn-primary" style="font-size:.7rem;">Kích hoạt</button>
            </div>
        @endforeach

        @foreach($pendingProducts as $purchase)
            <div class="order-row">
                <div>
                    <strong style="display:block; color:#123B59; font-size:.82rem;">Tài nguyên · {{ $purchase->product->title }}</strong>
                    <span style="font-size:.72rem; color:#61798A;">{{ $purchase->user->name }} · {{ $purchase->user->email }}</span>
                </div>
                <button wire:click="activateOrder('product', {{ $purchase->id }})" class="btn btn-primary" style="font-size:.7rem;">Kích hoạt</button>
            </div>
        @endforeach
    </div>
</div>
