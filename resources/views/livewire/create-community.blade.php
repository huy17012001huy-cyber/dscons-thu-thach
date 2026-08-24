<div class="create-page">
<style>
    .create-page { max-width: 1120px; margin: 0 auto; padding: 28px clamp(16px, 3vw, 30px) 64px; color: var(--text); }
    .create-hero { display: flex; align-items: end; justify-content: space-between; gap: 24px; padding-bottom: 22px; border-bottom: 1px solid var(--border); }
    .create-kicker { color: var(--green); font-size: 11px; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }
    .create-hero h1 { margin: 8px 0 7px; color: var(--text); font-size: clamp(27px, 3vw, 36px); letter-spacing: -.045em; line-height: 1.1; }
    .create-hero p { max-width: 650px; margin: 0; color: var(--text-muted); font-size: 14px; line-height: 1.65; }
    .create-back { color: var(--green); font-size: 13px; font-weight: 750; text-decoration: none; white-space: nowrap; }
    .create-layout { display: grid; grid-template-columns: minmax(0, 1fr) 278px; gap: 18px; margin-top: 22px; align-items: start; }
    .create-form { overflow: hidden; border: 1px solid #D5E4EA; border-radius: 18px; background: #fff; box-shadow: 0 4px 14px rgba(18,59,89,.05); }
    .create-form:focus-within { border-color: #A9CAD7; box-shadow: 0 0 0 3px rgba(31,119,190,.09), 0 8px 20px rgba(18,59,89,.07); }
    .create-form-section { padding: 22px 24px; border-bottom: 1px solid #E6EEF1; }
    .create-form-section:last-of-type { border-bottom: 0; }
    .create-section-heading { display: flex; align-items: start; gap: 11px; margin-bottom: 17px; }
    .create-step { width: 26px; height: 26px; flex: 0 0 auto; display: grid; place-items: center; border-radius: 9px; background: #E1F4F7; color: var(--green); font-size: 12px; font-weight: 850; }
    .create-section-heading h2 { margin: 0; color: var(--text); font-size: 16px; letter-spacing: -.02em; }
    .create-section-heading p { margin: 3px 0 0; color: var(--text-muted); font-size: 12px; line-height: 1.5; }
    .create-fields { display: grid; gap: 14px; }
    .create-fields.two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .create-field { display: grid; gap: 6px; color: var(--text); font-size: 12px; font-weight: 750; }
    .create-field small { color: var(--text-muted); font-size: 11px; font-weight: 500; }
    .create-field .form-control { min-height: 43px; border-radius: 11px; }
    .create-field .form-control:focus { border-color: #1F77BE; box-shadow: 0 0 0 3px rgba(31,119,190,.14); }
    .create-field textarea.form-control { min-height: 105px; resize: vertical; }
    .create-upload-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .create-upload { min-height: 106px; display: grid; align-content: center; gap: 5px; padding: 15px; border: 1px dashed #A9C9D6; border-radius: 13px; background: #F8FCFD; color: var(--text); cursor: pointer; font-size: 12px; font-weight: 750; transition: border-color .15s ease, background .15s ease, transform .15s ease; }
    .create-upload:hover, .create-upload:focus-within { border-color: var(--green); background: #EDF8FA; transform: translateY(-1px); }
    .create-upload svg { width: 20px; height: 20px; color: var(--green); }
    .create-upload span { overflow: hidden; color: var(--text-muted); font-size: 11px; font-weight: 500; text-overflow: ellipsis; white-space: nowrap; }
    .create-form-footer { display: flex; align-items: center; justify-content: flex-end; gap: 10px; padding: 17px 24px; background: #F8FBFC; }
    .create-submit { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 43px; padding: 10px 17px; border: 1px solid #1F77BE !important; border-radius: 12px !important; background: #1F77BE !important; color: #fff !important; font-weight: 800 !important; box-shadow: 0 5px 13px rgba(31,119,190,.2); transition: transform .15s ease, background .15s ease, box-shadow .15s ease; }
    .create-submit:hover { border-color: #125A96 !important; background: #125A96 !important; box-shadow: 0 8px 18px rgba(18,90,150,.25); transform: translateY(-1px); }
    .create-submit:disabled { cursor: wait; opacity: .65; transform: none; }
    .create-aside { display: grid; gap: 12px; }
    .create-aside-card { padding: 18px; border: 1px solid #D5E4EA; border-radius: 16px; background: #fff; box-shadow: 0 3px 10px rgba(18,59,89,.04); }
    .create-aside-card h2 { margin: 0 0 8px; color: var(--text); font-size: 15px; }
    .create-aside-card p { margin: 0; color: var(--text-muted); font-size: 12px; line-height: 1.6; }
    .create-checklist { display: grid; gap: 11px; margin-top: 14px; }
    .create-check { display: flex; align-items: start; gap: 8px; color: var(--text-muted); font-size: 12px; line-height: 1.45; }
    .create-check svg { width: 16px; height: 16px; flex: 0 0 auto; color: var(--green); }
    .create-note { border-color: #F4D69A; background: #FFFBF2; }
    .create-note h2 { color: #8A5A00; }
    @media (max-width: 850px) { .create-layout { grid-template-columns: 1fr; } .create-aside { grid-row: 1; grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 650px) { .create-page { padding: 20px 14px 44px; } .create-hero { display: block; } .create-back { display: inline-block; margin-top: 14px; } .create-form-section { padding: 18px 16px; } .create-fields.two, .create-upload-grid, .create-aside { grid-template-columns: 1fr; } .create-form-footer { display: grid; grid-template-columns: 1fr 1fr; padding: 14px 16px; } .create-form-footer .ds-btn { width: 100%; } }
</style>

    <header class="create-hero">
        <div>
            <div class="create-kicker">Creator application</div>
            <h1>Đề xuất một cộng đồng mới</h1>
            <p>Tạo một không gian riêng để chia sẻ kiến thức, xây dựng chương trình học và đồng hành cùng học viên.</p>
        </div>
        <a href="{{ route('communities') }}" class="create-back">← Khám phá cộng đồng</a>
    </header>

    <div class="create-layout">
        <form wire:submit="submit" class="create-form">
            <section class="create-form-section">
                <div class="create-section-heading"><span class="create-step">1</span><div><h2>Thông tin community</h2><p>Tên và định danh giúp học viên nhận ra bạn.</p></div></div>
                <div class="create-fields">
                    <div class="create-fields two">
                        <label class="create-field">Tên community <input wire:model.live="name" class="form-control" placeholder="Ví dụ: BIM Automation Lab">@error('name')<span class="field-error">{{ $message }}</span>@enderror</label>
                        <label class="create-field">Slug <input wire:model="slug" class="form-control" placeholder="bim-automation"><small>URL: /c/slug-cua-ban</small>@error('slug')<span class="field-error">{{ $message }}</span>@enderror</label>
                    </div>
                    <label class="create-field">Tagline <input wire:model="tagline" class="form-control" placeholder="Một câu mô tả ngắn gọn về community"></label>
                    <label class="create-field">Mô tả community <textarea wire:model="description" class="form-control" rows="4" placeholder="Community giúp thành viên đạt được điều gì?"></textarea>@error('description')<span class="field-error">{{ $message }}</span>@enderror</label>
                </div>
            </section>

            <section class="create-form-section">
                <div class="create-section-heading"><span class="create-step">2</span><div><h2>Nội dung và hướng giảng dạy</h2><p>Cho nền tảng biết bạn muốn giúp học viên tiến bộ ở đâu.</p></div></div>
                <div class="create-fields">
                    <label class="create-field">Chủ đề giảng dạy <input wire:model="teachingTopic" class="form-control" placeholder="BIM, quản trị dự án, thiết kế…">@error('teachingTopic')<span class="field-error">{{ $message }}</span>@enderror</label>
                    <label class="create-field">Chương trình dự kiến <textarea wire:model="programDescription" class="form-control" rows="4" placeholder="Khóa học, challenge hoặc lộ trình bạn muốn mở…"></textarea>@error('programDescription')<span class="field-error">{{ $message }}</span>@enderror</label>
                </div>
            </section>

            <section class="create-form-section">
                <div class="create-section-heading"><span class="create-step">3</span><div><h2>Gói Premium và thanh toán</h2><p>Các thông tin này chỉ là đề xuất để đội ngũ xem xét.</p></div></div>
                <div class="create-fields two">
                    <label class="create-field">Giá Premium dự kiến (VNĐ) <input wire:model="proposedPremiumPrice" type="number" min="0" class="form-control" placeholder="0"></label>
                    <label class="create-field">Tài khoản nhận thanh toán <input wire:model="proposedSepayAccount" class="form-control" placeholder="Tùy chọn"></label>
                    <label class="create-field">Ngân hàng <input wire:model="proposedSepayBank" class="form-control" placeholder="Tùy chọn"></label>
                </div>
            </section>

            <section class="create-form-section">
                <div class="create-section-heading"><span class="create-step">4</span><div><h2>Nhận diện community</h2><p>Logo và banner giúp trang giới thiệu có cá tính riêng.</p></div></div>
                <div class="create-upload-grid">
                    <label class="create-upload"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>Logo community<input wire:model="logo" type="file" accept="image/*" hidden>@if($logo)<span>{{ $logo->getClientOriginalName() }}</span>@else<span>PNG/JPG, tối đa 4MB</span>@endif</label>
                    <label class="create-upload"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="3"/><path d="m3 15 5-5 4 4 3-3 6 6"/></svg>Banner community<input wire:model="banner" type="file" accept="image/*" hidden>@if($banner)<span>{{ $banner->getClientOriginalName() }}</span>@else<span>Khuyến nghị 16:9, tối đa 8MB</span>@endif</label>
                </div>
            </section>

            <footer class="create-form-footer"><a href="{{ route('communities') }}" class="ds-btn" style="text-decoration:none;text-align:center;">Hủy</a><button wire:loading.attr="disabled" class="ds-btn create-submit" type="submit"><span wire:loading.remove>Gửi hồ sơ</span><span wire:loading>Đang gửi…</span><svg wire:loading.remove viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button></footer>
        </form>

        <aside class="create-aside">
            <div class="create-aside-card"><h2>Quy trình duyệt</h2><p>Đội ngũ nền tảng sẽ xem xét community trước khi publish để bảo đảm chất lượng nội dung và trải nghiệm học viên.</p><div class="create-checklist"><div class="create-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg><span>Hồ sơ được kiểm tra trong admin</span></div><div class="create-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg><span>Gói Free được tạo mặc định</span></div><div class="create-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg><span>Bạn trở thành owner sau khi duyệt</span></div></div></div>
            <div class="create-aside-card create-note"><h2>Trước khi gửi</h2><p>Chuẩn bị mô tả rõ đối tượng học viên, kết quả đầu ra và chương trình dự kiến. Thông tin thanh toán có thể bổ sung sau.</p></div>
        </aside>
    </div>
</div>
