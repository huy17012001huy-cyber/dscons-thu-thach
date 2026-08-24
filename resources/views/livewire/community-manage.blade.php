<div class="community-manage">
<style>
    .community-manage { max-width: 1120px; margin: 0 auto; padding: 30px clamp(16px, 3vw, 30px) 64px; color: var(--text); }
    .community-manage-header { margin-bottom: 22px; }
    .community-manage-header-main { display: flex; align-items: end; justify-content: space-between; gap: 20px; }
    .community-manage-kicker { display: flex; align-items: center; gap: 7px; margin: 0 0 8px; color: var(--green); font-size: 11px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
    .community-manage-kicker svg { width: 15px; height: 15px; }
    .community-manage-header h1 { margin: 0; color: var(--text); font-size: clamp(25px, 3vw, 30px); font-weight: 800; letter-spacing: -.035em; line-height: 1.15; text-wrap: balance; }
    .community-manage-header p { max-width: 620px; margin: 8px 0 0; color: var(--text-muted); font-size: 14px; line-height: 1.65; }
    .community-manage-command-bar { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 18px; padding: 8px; border: 1px solid #C9DEE8; border-radius: 16px; background: #F0F8FA; box-shadow: 0 4px 12px rgba(18,59,89,.045); }
    .community-manage-command-group { display: flex; align-items: center; gap: 7px; min-width: 0; }
    .community-manage-command-group.is-content { flex: 1 1 auto; }
    .community-manage-command-group.is-navigation { flex: 0 0 auto; }
    .community-manage-command { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; box-sizing: border-box; padding: 0 13px; border: 1px solid #AFCFDF; border-radius: 11px; background: #fff; color: #125A96; font-size: 13px; font-weight: 750; line-height: 1; text-decoration: none; white-space: nowrap; touch-action: manipulation; -webkit-tap-highlight-color: transparent; transition: background-color .16s ease, border-color .16s ease, color .16s ease, box-shadow .16s ease, transform .16s ease; }
    .community-manage-command svg { width: 17px; height: 17px; flex: 0 0 auto; }
    .community-manage-command.is-content { flex: 1 1 0; min-width: 0; }
    .community-manage-command.is-content:hover { border-color: #1F77BE; background: #1F77BE; color: #fff; box-shadow: 0 5px 12px rgba(31,119,190,.18); }
    .community-manage-command.is-secondary { background: #fff; color: #125A96; }
    .community-manage-command.is-secondary:hover { border-color: #1F77BE; background: #E1F4F7; color: #125A96; }
    .community-manage-command.is-feed { border-color: #D4E4EC; background: #F8FBFC; color: var(--text-muted); }
    .community-manage-command.is-feed:hover { border-color: #AFCFDF; background: #fff; color: #125A96; }
    .community-manage-command:focus-visible { outline: 3px solid rgba(243,148,2,.32); outline-offset: 2px; }
    .community-manage-command:active { transform: translateY(1px); box-shadow: none; }
    .community-manage-layout { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(280px, .75fr); align-items: start; gap: 16px; }
    .community-manage-card { border: 1px solid var(--border); border-radius: 18px; background: #fff; box-shadow: 0 2px 8px rgba(18,59,89,.035); }
    .community-manage-card-header { padding: 20px 22px 0; }
    .community-manage-card-header h2 { margin: 0; color: var(--text); font-size: 18px; letter-spacing: -.02em; }
    .community-manage-card-header p { margin: 6px 0 0; color: var(--text-muted); font-size: 13px; line-height: 1.55; }
    .community-manage-form { display: grid; gap: 16px; padding: 20px 22px 22px; }
    .community-manage-field { display: grid; gap: 7px; color: var(--text); font-size: 13px; font-weight: 750; }
    .community-manage-field .form-control { width: 100%; min-height: 44px; box-sizing: border-box; border: 1px solid #C6DBE5 !important; border-radius: 11px; background: #fff !important; color: var(--text); padding: 10px 12px; font: inherit; font-size: 14px; font-weight: 500; box-shadow: inset 0 1px 1px rgba(18,59,89,.02); }
    .community-manage-field .form-control:focus-visible { border-color: var(--green) !important; outline: none; box-shadow: 0 0 0 3px rgba(31,119,190,.15); }
    .community-manage-field textarea.form-control { min-height: 132px; resize: vertical; }
    .community-manage-upload-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .community-manage-upload { display: flex; min-height: 104px; flex-direction: column; justify-content: center; gap: 5px; box-sizing: border-box; padding: 15px; border: 1px dashed #A9C5D5; border-radius: 14px; background: #F8FCFD; color: var(--text); cursor: pointer; transition: border-color .15s ease, background .15s ease, box-shadow .15s ease; }
    .community-manage-upload:hover { border-color: var(--green); background: #F0F8FA; }
    .community-manage-upload:focus-within { border-color: var(--green); box-shadow: 0 0 0 3px rgba(31,119,190,.16); }
    .community-manage-upload svg { width: 21px; height: 21px; color: var(--green); }
    .community-manage-upload strong { font-size: 13px; }
    .community-manage-upload span { color: var(--text-muted); font-size: 11px; font-weight: 500; line-height: 1.45; }
    .community-manage-upload input { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; clip-path: inset(50%); }
    .community-manage-current-assets { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .community-manage-current-asset { display: flex; align-items: center; gap: 9px; min-width: 0; padding: 9px; border: 1px solid #E1ECF1; border-radius: 12px; background: #FBFDFE; }
    .community-manage-current-asset img { width: 38px; height: 38px; flex: 0 0 auto; border-radius: 9px; object-fit: cover; }
    .community-manage-current-asset img.is-logo { object-fit: contain; background: #EAF6F8; }
    .community-manage-current-asset span { display: block; overflow: hidden; color: var(--text-muted); font-size: 11px; text-overflow: ellipsis; white-space: nowrap; }
    .community-manage-remove { display: inline-flex; align-items: center; gap: 6px; margin-top: 4px; color: #A43C35; font-size: 11px; font-weight: 700; cursor: pointer; }
    .community-manage-remove input { accent-color: #A43C35; }
    .community-manage-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 2px; padding-top: 16px; border-top: 1px solid #E1ECF1; }
    .community-manage-save { display: inline-flex !important; align-items: center; justify-content: center; gap: 8px; min-width: 158px; min-height: 44px !important; border-color: #1F77BE !important; border-radius: 11px !important; background: #1F77BE !important; color: #fff !important; box-shadow: 0 6px 14px rgba(31,119,190,.20); font-size: 13px !important; }
    .community-manage-save svg { width: 17px; height: 17px; }
    .community-manage-save:hover:not(:disabled) { border-color: #125A96 !important; background: #125A96 !important; box-shadow: 0 8px 16px rgba(18,90,150,.25); }
    .community-manage-save:disabled { cursor: wait; opacity: .72; }
    .community-manage-side { display: grid; gap: 16px; }
    .community-manage-summary { padding: 20px 22px; }
    .community-manage-summary h2, .community-manage-members h2 { margin: 0; color: var(--text); font-size: 18px; letter-spacing: -.02em; }
    .community-manage-stat-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-top: 16px; }
    .community-manage-stat { padding: 13px; border: 1px solid #E1ECF1; border-radius: 12px; background: var(--bg-subtle, #F7FAFC); }
    .community-manage-stat span { display: block; color: var(--text-muted); font-size: 11px; }
    .community-manage-stat strong { display: block; margin-top: 4px; color: var(--text); font-size: 20px; letter-spacing: -.03em; }
    .community-manage-stat strong.is-active { color: var(--green); font-size: 13px; letter-spacing: 0; white-space: nowrap; }
    .community-manage-url { margin: 15px 0 0; padding-top: 14px; border-top: 1px solid var(--border); color: var(--text-muted); font-size: 12px; }
    .community-manage-url strong { display: block; margin-top: 4px; overflow: hidden; color: var(--text); font-size: 13px; text-overflow: ellipsis; white-space: nowrap; }
    .community-manage-plans { padding: 20px 22px; }
    .community-manage-plans-heading { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; margin-bottom: 6px; }
    .community-manage-plans h2 { margin: 0; color: var(--text); font-size: 18px; letter-spacing: -.02em; }
    .community-manage-plans-heading span { color: var(--text-muted); font-size: 11px; }
    .community-manage-plan { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 13px 0; border-top: 1px solid #E6EEF2; }
    .community-manage-plan-name { min-width: 0; color: var(--text); font-size: 14px; font-weight: 750; }
    .community-manage-plan-status { display: inline-flex; margin-top: 4px; padding: 3px 7px; border-radius: 999px; background: var(--green-light); color: var(--green); font-size: 10px; font-weight: 800; text-transform: uppercase; }
    .community-manage-plan-price { flex: 0 0 auto; color: var(--green); font-size: 14px; font-weight: 800; }
    .community-manage-empty { margin: 14px 0 0; color: var(--text-muted); font-size: 13px; line-height: 1.55; }
    .community-manage-members { margin-top: 16px; padding: 20px 22px; }
    .community-manage-members-heading { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; }
    .community-manage-members-heading span { color: var(--text-muted); font-size: 12px; }
    .community-manage-member-list { display: grid; margin-top: 12px; }
    .community-manage-member { display: flex; align-items: center; gap: 11px; padding: 11px 0; border-top: 1px solid #E6EEF2; }
    .community-manage-member img { width: 36px; height: 36px; flex: 0 0 auto; border: 1px solid #D8E6EC; border-radius: 50%; object-fit: cover; }
    .community-manage-member strong { display: block; overflow: hidden; color: var(--text); font-size: 14px; text-overflow: ellipsis; white-space: nowrap; }
    .community-manage-member span { display: block; margin-top: 2px; color: var(--text-muted); font-size: 12px; }
    @media (max-width: 900px) { .community-manage-layout { grid-template-columns: 1fr; } .community-manage-side { grid-template-columns: repeat(2, minmax(0, 1fr)); } .community-manage-command-bar { align-items: stretch; flex-direction: column; } .community-manage-command-group { width: 100%; } .community-manage-command-group.is-navigation { justify-content: flex-end; } }
    @media (max-width: 620px) { .community-manage { padding: 20px 14px 44px; } .community-manage-header-main { display: block; } .community-manage-command-group { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); } .community-manage-command-group.is-navigation { justify-content: stretch; } .community-manage-command.is-content:last-child { grid-column: span 2; } .community-manage-command { width: 100%; padding: 0 10px; } .community-manage-card-header, .community-manage-form, .community-manage-summary, .community-manage-plans, .community-manage-members { padding-left: 16px; padding-right: 16px; } .community-manage-upload-grid, .community-manage-current-assets, .community-manage-side { grid-template-columns: 1fr; } .community-manage-actions .ds-btn { width: 100%; } }
</style>

    <header class="community-manage-header">
        <div class="community-manage-header-main">
        <div>
            <div class="community-manage-kicker">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 4 7v5c0 5 3.4 8.7 8 9 4.6-.3 8-4 8-9V7l-8-4Z"/><path d="M9 12h6M12 9v6"/></svg>
                Quản trị cộng đồng
            </div>
            <h1>Quản lý {{ $community->name }}</h1>
            <p>Cập nhật thông tin hiển thị, nhận diện và theo dõi các thành viên cùng gói thành viên.</p>
        </div>
        </div>
        <nav class="community-manage-command-bar" aria-label="Thao tác quản lý community">
            <div class="community-manage-command-group is-content">
                <a href="{{ community_route('manage.courses') }}" class="community-manage-command is-content">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.5 5.5A2.5 2.5 0 0 1 6 3h11.5A2.5 2.5 0 0 1 20 5.5v13A2.5 2.5 0 0 1 17.5 21H6a2.5 2.5 0 0 1-2.5-2.5v-13Z"/><path d="M8 7h7M8 11h7M8 15h4"/></svg>
                    <span>Khóa học</span>
                </a>
                <a href="{{ community_route('manage.challenges') }}" class="community-manage-command is-content">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3h8l1 4 3 1-2 5-3-1-1 8H10l-1-8-3 1-2-5 3-1 1-4Z"/><path d="M10 7h4"/></svg>
                    <span>Challenge</span>
                </a>
                <a href="{{ community_route('manage.events') }}" class="community-manage-command is-content">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M7 3v4M17 3v4M3 10h18M8 14h.01M12 14h.01M16 14h.01"/></svg>
                    <span>Sự kiện</span>
                </a>
            </div>
            <div class="community-manage-command-group is-navigation">
                @can('admin')
                <a href="{{ community_route('manage.settings') }}" class="community-manage-command is-secondary">
                    <x-icon name="settings" size="16" />
                    <span>Cài đặt community</span>
                </a>
                <a href="{{ community_route('manage.recruitment') }}" class="community-manage-command is-secondary">
                    <x-icon name="briefcase" size="16" />
                    <span>CV & tuyển dụng</span>
                </a>
                @endcan
                <a href="{{ route('community.preview', $community->slug) }}" class="community-manage-command is-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12s3.3-6 9-6 9 6 9 6-3.3 6-9 6-9-6-9-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                    <span>Xem giới thiệu</span>
                </a>
                <a href="{{ community_route('feed') }}" class="community-manage-command is-feed">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                    <span>Bảng tin</span>
                </a>
            </div>
        </nav>
    </header>

    <div class="community-manage-layout">
        <form wire:submit="save" class="community-manage-card">
            <div class="community-manage-card-header">
                <h2>Thông tin hiển thị</h2>
                <p>Những nội dung này xuất hiện ở trang giới thiệu cộng đồng.</p>
            </div>
            <div class="community-manage-form">
                <label class="community-manage-field" for="community-name">
                    Tên cộng đồng
                    <input id="community-name" name="name" wire:model="name" class="form-control" autocomplete="organization" aria-describedby="community-name-error" @error('name') aria-invalid="true" @enderror>
                    @error('name')<span id="community-name-error" class="field-error" role="alert">{{ $message }}</span>@enderror
                </label>
                <label class="community-manage-field" for="community-tagline">
                    Mục tiêu ngắn gọn
                    <input id="community-tagline" name="tagline" wire:model="tagline" class="form-control" maxlength="255" autocomplete="off" aria-describedby="community-tagline-help">
                    <span id="community-tagline-help" style="color:var(--text-muted);font-size:11px;font-weight:500;">Dòng tiêu đề nổi bật tại phần “Mục tiêu của cộng đồng”.</span>
                </label>
                <label class="community-manage-field" for="community-description">
                    Mục tiêu / giới thiệu chi tiết
                    <textarea id="community-description" name="description" wire:model="description" class="form-control" rows="6" maxlength="5000" autocomplete="off"></textarea>
                </label>

                <div class="community-manage-upload-grid">
                    <label class="community-manage-upload">
                        <input wire:model="logo" type="file" accept="image/*" aria-label="Chọn logo cộng đồng">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                        <strong>Đổi logo</strong>
                        <span>{{ $logo ? $logo->getClientOriginalName() : 'PNG hoặc JPG, tối đa 4 MB' }}</span>
                    </label>
                    <label class="community-manage-upload">
                        <input wire:model="banner" type="file" accept="image/*" aria-label="Chọn banner cộng đồng">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="3"/><path d="m3 15 5-5 4 4 3-3 6 6"/></svg>
                        <strong>Đổi ảnh bìa</strong>
                        <span>{{ $banner ? $banner->getClientOriginalName() : 'Khuyến nghị 16:9, tối đa 8 MB' }}</span>
                    </label>
                </div>

                @if($community->logo_path || $community->banner_path)
                    <div class="community-manage-current-assets" aria-label="Ảnh đang sử dụng">
                        @if($community->logo_path)
                            <div class="community-manage-current-asset">
                                <img class="is-logo" src="{{ asset('storage/'.$community->logo_path) }}" alt="Logo hiện tại" width="38" height="38">
                                <div style="min-width:0;flex:1;"><strong style="display:block;color:var(--text);font-size:12px;">Logo hiện tại</strong><span>{{ basename($community->logo_path) }}</span><label class="community-manage-remove"><input type="checkbox" wire:model="removeLogo"> Gỡ logo khi lưu</label></div>
                            </div>
                        @endif
                        @if($community->banner_path)
                            <div class="community-manage-current-asset">
                                <img src="{{ asset('storage/'.$community->banner_path) }}" alt="Ảnh bìa hiện tại" width="38" height="38">
                                <div style="min-width:0;flex:1;"><strong style="display:block;color:var(--text);font-size:12px;">Ảnh bìa hiện tại</strong><span>{{ basename($community->banner_path) }}</span><label class="community-manage-remove"><input type="checkbox" wire:model="removeBanner"> Gỡ ảnh bìa khi lưu</label></div>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="community-manage-actions">
                    <button class="ds-btn ds-btn-primary community-manage-save" type="submit" wire:loading.attr="disabled" wire:target="save,logo,banner">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 3h12l3 3v15H5z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/></svg>
                        <span wire:loading.remove wire:target="save">Lưu thay đổi</span><span wire:loading wire:target="save" role="status" aria-live="polite">Đang lưu…</span>
                    </button>
                </div>
            </div>
        </form>

        <aside class="community-manage-side" aria-label="Tổng quan cộng đồng">
            <section class="community-manage-card community-manage-summary">
                <h2>Tổng quan</h2>
                <div class="community-manage-stat-grid">
                    <div class="community-manage-stat"><span>Thành viên</span><strong>{{ number_format($community->users()->count()) }}</strong></div>
                    <div class="community-manage-stat"><span>Trạng thái</span><strong class="is-active">{{ $community->isPublished() ? 'Online' : 'Bản nháp' }}</strong></div>
                </div>
                <p class="community-manage-url">Đường dẫn cộng đồng<strong>/c/{{ $community->slug }}</strong></p>
            </section>

            <section class="community-manage-card community-manage-plans">
                <div class="community-manage-plans-heading"><h2>Gói thành viên</h2><span>{{ $plans->count() }} gói</span></div>
                @forelse($plans as $plan)
                    @php($planStatus = ['draft' => 'Bản nháp', 'pending_review' => 'Chờ duyệt', 'published' => 'Đang hiển thị', 'archived' => 'Đã lưu trữ'][$plan->status] ?? $plan->status)
                    <div class="community-manage-plan">
                        <div class="community-manage-plan-name">{{ $plan->name }}<span class="community-manage-plan-status">{{ $planStatus }}</span></div>
                        <strong class="community-manage-plan-price">{{ $plan->price ? number_format($plan->price).'đ' : 'Miễn phí' }}</strong>
                    </div>
                @empty
                    <p class="community-manage-empty">Chưa có gói thành viên được tạo.</p>
                @endforelse
            </section>
        </aside>
    </div>

    <section class="community-manage-card community-manage-members">
        <div class="community-manage-members-heading"><h2>Thành viên gần đây</h2><span>{{ $members->count() }} người hiển thị</span></div>
        <div class="community-manage-member-list">
            @forelse($members as $member)
                <div class="community-manage-member">
                    <img src="{{ $member->avatar_url }}" alt="" width="36" height="36" loading="lazy">
                    <div><strong>{{ $member->name }}</strong><span>{{ $member->pivot->role }}</span></div>
                </div>
            @empty
                <p class="community-manage-empty">Chưa có thành viên nào trong cộng đồng này.</p>
            @endforelse
        </div>
    </section>
</div>
