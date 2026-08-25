<div style="max-width:820px;margin:0 auto;padding:32px 18px 64px;">
    <div style="margin-bottom:22px;">
        <p style="margin:0 0 7px;color:#1F77BE;font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;">Quản trị community</p>
        <h1 style="margin:0;color:#123B59;font-size:clamp(25px,4vw,34px);letter-spacing:-.035em;">Cài đặt {{ $community->name }}</h1>
        <p style="margin:9px 0 0;color:#61798A;line-height:1.6;">Bật hoặc tắt các phân hệ của community. Dữ liệu cũ được giữ lại và không bị xóa.</p>
    </div>

    <form wire:submit="save" style="display:grid;gap:14px;">
        <section style="padding:22px;border:1px solid #C9DEE8;border-radius:18px;background:#fff;box-shadow:0 8px 24px rgba(18,59,89,.06);">
            <h2 style="margin:0;color:#123B59;font-size:18px;">Tính năng theo community</h2>
            <p style="margin:6px 0 18px;color:#61798A;font-size:13px;line-height:1.55;">Chỉ quản trị viên của {{ $community->name }} mới thay đổi được các cài đặt này.</p>

            <label style="display:flex;align-items:flex-start;gap:12px;padding:15px 0;border-top:1px solid #E7EEF1;cursor:pointer;">
                <input type="checkbox" wire:model="hasCv" style="margin-top:3px;accent-color:#1F77BE;">
                <span><strong style="display:block;color:#183B55;font-size:14px;">Hồ sơ CV kỹ sư</strong><small style="display:block;margin-top:4px;color:#61798A;line-height:1.5;">Cho phép kỹ sư tạo, chỉnh sửa và chọn CV công khai trong community này.</small></span>
            </label>
            <label style="display:flex;align-items:flex-start;gap:12px;padding:15px 0;border-top:1px solid #E7EEF1;cursor:pointer;">
                <input type="checkbox" wire:model="hasRecruitment" style="margin-top:3px;accent-color:#1F77BE;">
                <span><strong style="display:block;color:#183B55;font-size:14px;">Tuyển dụng DSCons Talent</strong><small style="display:block;margin-top:4px;color:#61798A;line-height:1.5;">Cho phép recruiter tìm ứng viên và gửi yêu cầu liên hệ trong community này.</small></span>
            </label>
        </section>

        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
            <a href="{{ community_route('manage') }}" class="btn btn-ghost" style="text-decoration:none;">← Về quản trị community</a>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Lưu cài đặt</button>
        </div>
    </form>
</div>
