<section style="max-width:720px;margin:0 auto;">
    <div class="page-heading"><span class="section-mark">Recruiter</span><h1>Thiết lập tài khoản nhà tuyển dụng</h1><p>Xác minh thông tin công ty để bắt đầu tìm ứng viên BIM/MEP.</p></div>
    <form wire:submit="save" class="card" style="padding:1.25rem;display:grid;gap:1rem;">
        <label>Tên công ty<input wire:model="companyName" class="form-control" placeholder="Công ty TNHH...">@error('companyName')<small class="compose-error">{{ $message }}</small>@enderror</label>
        <label>Email tuyển dụng<input wire:model="businessEmail" type="email" class="form-control" placeholder="hr@company.com">@error('businessEmail')<small class="compose-error">{{ $message }}</small>@enderror</label>
        <label>Website<input wire:model="website" class="form-control" placeholder="https://company.com">@error('website')<small class="compose-error">{{ $message }}</small>@enderror</label>
        <label>Lĩnh vực<input wire:model="industry" class="form-control"></label>
        <label>Giới thiệu<textarea wire:model="description" class="form-control" rows="5" placeholder="Bạn đang tuyển cho loại dự án nào?"></textarea></label>
        <div style="display:flex;justify-content:flex-end;"><button class="btn btn-primary" type="submit">Lưu và gửi xác minh</button></div>
    </form>
</section>
