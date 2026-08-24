<div class="admin-dashboard-page">
<style>
    .admin-dashboard-page { max-width: 1120px; margin: 0 auto; }
    .admin-dashboard-page .admin-stat { min-height: 112px; display: grid; align-content: center; border-radius: 16px; }
    .admin-dashboard-page .admin-stat p:first-child { font-variant-numeric: tabular-nums; }
    .admin-dashboard-page a.card { border-radius: 16px; transition: transform .16s ease, border-color .16s ease, box-shadow .16s ease, background-color .16s ease; }
    .admin-dashboard-page a.card:hover { transform: translateY(-2px); border-color: #8FB9CB; background: #FBFDFE; box-shadow: 0 10px 22px rgba(18,59,89,.09); }
    .admin-dashboard-page a.card:focus-visible { outline: 3px solid rgba(31,119,190,.20); outline-offset: 2px; }
</style>

    <h1 style="font-size:1.25rem; font-weight:800; color:#1A1A1A; margin-bottom:1.25rem;">■ Admin Dashboard</h1>

    {{-- Stats --}}
    <div class="grid gap-4 mb-6" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
        <div class="admin-stat card text-center">
            <p style="font-size:1.5rem; font-weight:800; color:#d17856;">{{ $totalUsers }}</p>
            <p style="font-size:0.8rem; color:#5C5C66;">Thành viên</p>
        </div>
        <div class="admin-stat card text-center">
            <p style="font-size:1.5rem; font-weight:800; color:#1A1A1A;">{{ $totalPosts }}</p>
            <p style="font-size:0.8rem; color:#5C5C66;">Bài viết</p>
        </div>
        <div class="admin-stat card text-center">
            <p style="font-size:1.5rem; font-weight:800; color:{{ $pendingReports > 0 ? '#DC2626' : '#059669' }};">{{ $pendingReports }}</p>
            <p style="font-size:0.8rem; color:#5C5C66;">Báo cáo chờ</p>
        </div>
        <div class="admin-stat card text-center">
            <p style="font-size:1.5rem; font-weight:800; color:{{ $pendingCot > 0 ? '#d17856' : '#5C5C66' }};">{{ $pendingCot }}</p>
            <p style="font-size:0.8rem; color:#5C5C66;">CỐT chờ duyệt</p>
        </div>
    </div>

    {{-- Navigation --}}
    <div class="grid gap-3" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
        <a href="{{ community_route('manage') }}" class="admin-shortcut card" style="text-decoration:none; transition:border-color 0.15s;">
            <div class="flex items-center gap-3">
                <span style="width:1.5rem;height:1.5rem;display:grid;place-items:center;color:#1F77BE;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" style="width:1.35rem;height:1.35rem;"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06A2 2 0 1 1 7.04 4.3l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09A1.65 1.65 0 0 0 15 4.6a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09A1.65 1.65 0 0 0 19.4 15Z"/></svg>
                </span>
                <div>
                    <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">Cấu hình community hiện tại</p>
                    <p style="font-size:0.75rem; color:#5C5C66;">Sửa mục tiêu, ảnh bìa, khóa học và Challenge</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.communities') }}" class="admin-shortcut card" style="text-decoration:none; transition:border-color 0.15s;">
            <div class="flex items-center gap-3">
                <span style="width:1.5rem;height:1.5rem;display:grid;place-items:center;color:#1F77BE;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" style="width:1.35rem;height:1.35rem;"><path d="M4 19h16M4 5h16M7 5v14M17 5v14M10 9h4M10 13h4"/></svg>
                </span>
                <div>
                    <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">Duyệt &amp; quản lý các community</p>
                    <p style="font-size:0.75rem; color:#5C5C66;">Xem hồ sơ, phê duyệt và quản lý toàn nền tảng</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.users') }}" class="admin-shortcut card" style="text-decoration:none; transition:border-color 0.15s;">
            <div class="flex items-center gap-3">
                <span style="font-size:1.5rem;">■</span>
                <div>
                    <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">Quản lý người dùng</p>
                    <p style="font-size:0.75rem; color:#5C5C66;">Ban, toggle admin/mod, xem thông tin</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.recruitment') }}" class="admin-shortcut card" style="text-decoration:none; transition:border-color 0.15s; border-color:#9CCDB8; background:#F6FCF8;">
            <div class="flex items-center gap-3">
                <span style="width:1.5rem;height:1.5rem;display:grid;place-items:center;color:#1B9B75;">
                    <x-icon name="briefcase" size="22" />
                </span>
                <div>
                    <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">Quản lý tuyển dụng</p>
                    <p style="font-size:0.75rem; color:#5C5C66;">Duyệt recruiter và xem recruiter đã kết nối CV nào</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.topics') }}" class="card" style="text-decoration:none;">
            <div class="flex items-center gap-3">
                <span style="font-size:1.5rem;">🏷️</span>
                <div>
                    <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">Quản lý Topics</p>
                    <p style="font-size:0.75rem; color:#5C5C66;">Thêm/sửa/xóa loại bài viết</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.courses') }}" class="card" style="text-decoration:none;">
            <div class="flex items-center gap-3">
                <span style="font-size:1.5rem;">▦</span>
                <div>
                    <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">Quản lý khóa học</p>
                    <p style="font-size:0.75rem; color:#5C5C66;">Tạo, xây dựng, publish khóa học</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.products') }}" class="card" style="text-decoration:none;">
            <div class="flex items-center gap-3">
                <span style="font-size:1.5rem;">▣</span>
                <div>
                    <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">Quản lý sản phẩm</p>
                    <p style="font-size:0.75rem; color:#5C5C66;">Tạo, chỉnh sửa sản phẩm Marketplace</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.challenges') }}" class="card" style="text-decoration:none;">
            <div class="flex items-center gap-3">
                <span style="font-size:1.5rem;">⚔️</span>
                <div>
                    <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">Quản lý Challenge</p>
                    <p style="font-size:0.75rem; color:#5C5C66;">Tạo, sửa, xóa challenge và tasks</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.orders') }}" class="card" style="text-decoration:none; border-color:#B8D7E6; background:#F7FCFD;">
            <div class="flex items-center gap-3">
                <span style="font-size:1.5rem;">🎁</span>
                <div>
                    <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">Đơn hàng &amp; cấp quyền</p>
                    <p style="font-size:0.75rem; color:#5C5C66;">Kích hoạt đơn thủ công hoặc tặng quyền học viên</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.events') }}" class="card" style="text-decoration:none;">
            <div class="flex items-center gap-3">
                <span style="font-size:1.5rem;">◷</span>
                <div>
                    <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">Quản lý sự kiện</p>
                    <p style="font-size:0.75rem; color:#5C5C66;">Tạo lịch meeting theo khóa học hoặc Challenge</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.cot') }}" class="card" style="text-decoration:none;">
            <div class="flex items-center gap-3">
                <span style="font-size:1.5rem;">★</span>
                <div>
                    <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">Duyệt CỐT</p>
                    <p style="font-size:0.75rem; color:#5C5C66;">Approve/reject bài viết CỐT</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.reports') }}" class="card" style="text-decoration:none;">
            <div class="flex items-center gap-3">
                <span style="font-size:1.5rem;">▲</span>
                <div>
                    <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">Báo cáo vi phạm</p>
                    <p style="font-size:0.75rem; color:#5C5C66;">Xem và xử lý báo cáo từ thành viên</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.feedbacks') }}" class="card" style="text-decoration:none;">
            <div class="flex items-center gap-3">
                <span style="font-size:1.5rem;">📬</span>
                <div>
                    <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">Góp ý & Khiếu nại</p>
                    <p style="font-size:0.75rem; color:#5C5C66;">Xem feedback từ thành viên</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.settings') }}" class="card" style="text-decoration:none;">
            <div class="flex items-center gap-3">
                <span style="font-size:1.5rem;">✉️</span>
                <div>
                    <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">Cài đặt email</p>
                    <p style="font-size:0.75rem; color:#5C5C66;">Xác minh email, nội dung & gửi thử</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.login-logs') }}" class="card" style="text-decoration:none;">
            <div class="flex items-center gap-3">
                <span style="font-size:1.5rem;">🔐</span>
                <div>
                    <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">Lịch sử đăng nhập</p>
                    <p style="font-size:0.75rem; color:#5C5C66;">IP &amp; thiết bị mỗi lần login — truy nick ảo</p>
                </div>
            </div>
        </a>
    </div>
</div>
