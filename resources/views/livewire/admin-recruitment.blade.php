<section class="admin-recruitment-page">
    <style>
        .admin-recruitment-page { max-width: 1180px; margin: 0 auto; }
        .recruitment-admin-header { display:flex; justify-content:space-between; gap:1rem; align-items:flex-start; margin-bottom:1.25rem; }
        .recruitment-admin-header h1 { margin:0; color:#102A3B; font-size:clamp(1.45rem,2vw,2rem); letter-spacing:-.03em; }
        .recruitment-admin-header p { margin:.35rem 0 0; color:#61798A; line-height:1.6; }
        .recruitment-admin-grid { display:grid; grid-template-columns:minmax(0,1.2fr) minmax(290px,.8fr); gap:1rem; align-items:start; }
        .recruitment-admin-card { padding:1rem; border:1px solid #D4E1E8; border-radius:16px; background:#FFFFFF; box-shadow:0 2px 8px rgba(18,59,89,.04); }
        .recruitment-admin-card h2 { margin:0 0 .7rem; color:#102A3B; font-size:1rem; }
        .recruitment-admin-card > p { color:#61798A; font-size:.82rem; line-height:1.55; }
        .recruitment-admin-table-wrap { overflow-x:auto; border:1px solid #D4E1E8; border-radius:12px; }
        .recruitment-admin-table { width:100%; border-collapse:collapse; min-width:760px; }
        .recruitment-admin-table th, .recruitment-admin-table td { padding:.7rem .75rem; border-bottom:1px solid #E7F0F5; text-align:left; vertical-align:top; }
        .recruitment-admin-table th { background:#F7FAFC; color:#61798A; font-size:.68rem; letter-spacing:.04em; text-transform:uppercase; }
        .recruitment-admin-table td { color:#29485B; font-size:.8rem; }
        .recruitment-admin-table tr:last-child td { border-bottom:0; }
        .connection-status { display:inline-flex; padding:.25rem .5rem; border-radius:999px; font-size:.68rem; font-weight:800; }
        .connection-status.accepted { background:#DCEFE6; color:#157456; }
        .connection-status.pending { background:#FFF1C9; color:#8A5B00; }
        .connection-status.rejected, .connection-status.expired { background:#FDE5E1; color:#A63B2B; }
        .recruitment-admin-form { display:grid; gap:.6rem; }
        .recruitment-admin-form label { display:grid; gap:.3rem; color:#29485B; font-size:.75rem; font-weight:750; }
        .recruitment-admin-form input, .recruitment-admin-form textarea { width:100%; border:1px solid #D4E1E8; border-radius:9px; padding:.65rem .7rem; background:#FFFFFF; color:#102A3B; font:inherit; }
        .recruitment-admin-form .form-row { display:grid; grid-template-columns:1fr 1fr; gap:.55rem; }
        .recruitment-admin-empty { padding:1.5rem; color:#6D766F; text-align:center; font-size:.82rem; }
        @media (max-width: 850px) {
            .recruitment-admin-grid { grid-template-columns:1fr; }
            .recruitment-admin-header { display:block; }
        }
    </style>

    <header class="recruitment-admin-header">
        <div>
            <span class="section-mark">DSCons Talent</span>
            <h1>Quản lý nhà tuyển dụng</h1>
            <p>Duyệt doanh nghiệp, cấu hình gói credit và theo dõi recruiter nào đã kết nối với CV nào.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost" style="text-decoration:none;white-space:nowrap;">← Về Admin</a>
    </header>

    <div class="recruitment-admin-card" style="margin-bottom:1rem;">
        <h2>Quan hệ recruiter và CV</h2>
        <p>Chỉ hiển thị dữ liệu quản trị của mối quan hệ. Thông tin liên hệ cá nhân không được đưa vào bảng này.</p>
        <div class="recruitment-admin-table-wrap">
            <table class="recruitment-admin-table">
                <caption class="sr-only">Danh sách kết nối tuyển dụng</caption>
                <thead>
                    <tr>
                        <th>Nhà tuyển dụng</th>
                        <th>Ứng viên ẩn danh</th>
                        <th>CV</th>
                        <th>Trạng thái</th>
                        <th>Thời điểm</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($connections as $connection)
                        @php
                            $recruiterProfile = $connection->recruiter?->recruiterProfile;
                            $engineerProfile = $connection->engineer?->engineerProfile;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $recruiterProfile?->company_name ?? 'Recruiter #'.$connection->recruiter_id }}</strong>
                            <small style="display:block;color:#61798A;margin-top:.2rem;">User #{{ $connection->recruiter_id }}</small>
                            </td>
                            <td>
                                <strong>{{ $engineerProfile?->anonymized_code ?? 'Kỹ sư #'.$connection->engineer_id }}</strong>
                                <small style="display:block;color:#61798A;margin-top:.2rem;">{{ $engineerProfile?->headline ?: 'BIM/MEP engineer' }}</small>
                            </td>
                            <td>
                                <strong>{{ $connection->cv?->title ?? 'CV đã xoá' }}</strong>
                                <small style="display:block;color:#61798A;margin-top:.2rem;">{{ $connection->cv?->template ?: 'N/A' }}</small>
                            </td>
                            <td><span class="connection-status {{ $connection->status }}">{{ match($connection->status) { 'accepted' => 'Đã chấp thuận', 'pending' => 'Đang chờ', 'rejected' => 'Từ chối', 'expired' => 'Hết hạn', default => $connection->status } }}</span></td>
                            <td>
                                <span>{{ $connection->created_at?->format('d/m/Y H:i') }}</span>
                                @if($connection->responded_at)<small style="display:block;color:#61798A;margin-top:.2rem;">Phản hồi: {{ $connection->responded_at->format('d/m/Y H:i') }}</small>@endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="recruitment-admin-empty">Chưa có yêu cầu kết nối nào.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="recruitment-admin-card" style="margin-bottom:1rem;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;">
            <div>
                <h2>Toàn bộ CV kỹ sư trong community</h2>
                <p>Admin được xem cả bản nháp và bản công khai để kiểm duyệt, hỗ trợ và xử lý quyền riêng tư. Khu vực này không hiển thị cho recruiter.</p>
            </div>
            <span class="connection-status accepted">{{ $engineerCvs->count() }} CV</span>
        </div>
        <div class="recruitment-admin-table-wrap">
            <table class="recruitment-admin-table">
                <caption class="sr-only">Danh sách toàn bộ CV kỹ sư</caption>
                <thead><tr><th>Kỹ sư</th><th>Chuyên môn</th><th>Kỹ năng / kinh nghiệm</th><th>Liên hệ</th><th>Trạng thái</th><th>Cập nhật</th></tr></thead>
                <tbody>
                    @forelse($engineerCvs as $cv)
                        @php($profile = $cvProfiles->get($cv->user_id))
                        <tr>
                            <td><strong>{{ $cv->user?->name ?? 'User #'.$cv->user_id }}</strong><small style="display:block;color:#61798A;margin-top:.2rem;">{{ $cv->title }} · {{ $cv->user?->email }}</small></td>
                            <td><strong>{{ $profile?->headline ?: 'Chưa có tiêu đề' }}</strong><small style="display:block;color:#61798A;margin-top:.2rem;">{{ $profile?->discipline ?: 'BIM/MEP' }} · {{ $profile?->years_experience ?? 0 }} năm</small></td>
                            <td><div style="display:flex;gap:.25rem;flex-wrap:wrap;max-width:300px;">@foreach(collect($cv->skills())->take(8) as $skill)<span style="padding:.2rem .35rem;border-radius:999px;background:#EEF7FA;color:#236684;font-size:.65rem;">{{ is_array($skill) ? ($skill['name'] ?? '') : $skill }}</span>@endforeach</div></td>
                            <td><span>{{ $profile?->contact_email ?: 'Chưa có email' }}</span><small style="display:block;color:#61798A;margin-top:.2rem;">{{ $profile?->contact_phone ?: 'Chưa có số điện thoại' }}</small></td>
                            <td><span class="connection-status {{ $cv->status === 'published' ? 'accepted' : 'pending' }}">{{ $cv->status === 'published' ? 'Công khai' : 'Bản nháp' }}</span><a href="{{ community_route('manage.recruitment.preview.cv', ['cv' => $cv->id]) }}" style="display:block;width:max-content;margin-top:.45rem;color:#125A96;font-size:.72rem;font-weight:800;text-decoration:none;">Xem như ứng viên →</a></td>
                            <td>{{ $cv->updated_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="recruitment-admin-empty">Chưa có CV kỹ sư trong community này.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="recruitment-admin-grid">
        <div class="recruitment-admin-card">
            <h2>Xem trước giao diện recruiter</h2>
            <p>Chọn một nhà tuyển dụng đã xác minh để kiểm tra đúng giao diện họ thấy. Chế độ này chỉ đọc và không dùng credit.</p>
            @forelse($verifiedRecruiters as $profile)
                <div style="display:flex;align-items:center;justify-content:space-between;gap:.7rem;padding:.65rem 0;border-bottom:1px solid #E7F0F5;">
                    <span style="font-size:.8rem;color:#29485B;"><strong style="display:block;color:#102A3B;">{{ $profile->company_name }}</strong><small style="color:#61798A;">{{ $profile->business_email }}</small></span>
                    <a href="{{ community_route('manage.recruitment.preview.recruiter', ['recruiter' => $profile->id]) }}" class="btn btn-ghost" style="padding:.4rem .55rem;font-size:.7rem;text-decoration:none;white-space:nowrap;">Xem như recruiter</a>
                </div>
            @empty
                <p class="recruitment-admin-empty">Chưa có recruiter nào đã xác minh để xem trước.</p>
            @endforelse
        </div>

        <div class="recruitment-admin-card">
            <h2>Nhà tuyển dụng chờ duyệt</h2>
            @forelse($pendingRecruiters as $profile)
                <div style="display:flex;justify-content:space-between;gap:1rem;padding:.75rem 0;border-bottom:1px solid #EEE9DF;">
                    <div>
                        <strong style="color:#17251F;">{{ $profile->company_name }}</strong>
                        <p style="margin:.2rem 0;color:#6D766F;font-size:.8rem;">{{ $profile->business_email }} · User #{{ $profile->user_id }}</p>
                        @if($profile->description)<p style="margin:0;color:#6D766F;font-size:.8rem;">{{ $profile->description }}</p>@endif
                    </div>
                    <span style="display:flex;gap:.4rem;white-space:nowrap;">
                        <button type="button" wire:click="approve({{ $profile->id }})" class="btn btn-primary" style="padding:.4rem .6rem;font-size:.72rem;">Duyệt</button>
                        <button type="button" wire:click="reject({{ $profile->id }})" class="btn btn-ghost" style="padding:.4rem .6rem;font-size:.72rem;">Từ chối</button>
                    </span>
                </div>
            @empty
                <p class="recruitment-admin-empty">Không có hồ sơ chờ duyệt.</p>
            @endforelse
        </div>

        <div class="recruitment-admin-card">
            <h2>Tạo gói credit</h2>
            <form wire:submit="savePlan" class="recruitment-admin-form">
                <label>Tên gói<input wire:model="planName" type="text" autocomplete="off" required></label>
                <label>Mô tả<textarea wire:model="planDescription" rows="2"></textarea></label>
                <div class="form-row">
                    <label>Credit<input wire:model="planCredits" type="number" min="0" inputmode="numeric" required></label>
                    <label>Số ngày<input wire:model="planDuration" type="number" min="1" inputmode="numeric"></label>
                </div>
                <label>Giá VNĐ<input wire:model="planPrice" type="number" min="0" inputmode="numeric" required></label>
                <button type="submit" class="btn btn-primary">Tạo gói</button>
            </form>
            <h2 style="margin-top:1.25rem;">Gói hiện có</h2>
            @foreach($plans as $plan)
                <div style="display:flex;justify-content:space-between;gap:.5rem;padding:.55rem 0;border-bottom:1px solid #EEE9DF;font-size:.78rem;">
                    <span>{{ $plan->name }} · {{ $plan->contact_credits }} credit</span>
                    <button type="button" wire:click="togglePlan({{ $plan->id }})" class="btn btn-ghost" style="padding:.25rem .45rem;font-size:.68rem;">{{ $plan->is_active ? 'Tắt' : 'Bật' }}</button>
                </div>
            @endforeach
        </div>
    </div>
</section>
