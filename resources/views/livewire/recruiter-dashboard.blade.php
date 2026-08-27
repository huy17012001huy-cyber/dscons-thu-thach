<section class="talent-dashboard" x-data>
    @if($isAdminPreview)
        <div class="talent-admin-preview" role="status"><div><strong>Chế độ xem trước của admin</strong><span>Bạn đang xem giao diện nhà tuyển dụng với dữ liệu ẩn danh. Không thể gửi yêu cầu liên hệ hoặc dùng credit từ màn hình này.</span></div><a href="{{ community_route('manage.recruitment') }}">Quay lại quản lý Talent</a></div>
    @endif
    @php($connectionByEngineer = $connections->keyBy('engineer_id'))
    <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.15rem;">
        <div><div class="talent-kicker">{{ brand()->name }} · Talent</div><h1 class="talent-heading">Tìm ứng viên phù hợp</h1><p class="talent-subtitle" style="margin:.45rem 0 0;">Chọn tiêu chí nhanh, xem năng lực ẩn danh và gửi yêu cầu khi bạn sẵn sàng.</p></div>
        <div style="display:flex;gap:.45rem;flex-wrap:wrap;align-items:center;"><span class="talent-status"><x-icon name="check-circle" size="13" /> Recruiter đã xác minh</span>@if($isAdminPreview)<span class="talent-btn talent-btn-secondary" style="min-height:36px;"><x-icon name="credit-card" size="15" /> {{ $creditSummary['available'] }} credit</span>@else<a href="{{ community_route('recruiter.plans') }}" class="talent-btn talent-btn-secondary" style="min-height:36px;"><x-icon name="credit-card" size="15" /> {{ $creditSummary['available'] }} credit</a>@endif</div>
    </div>

    <div class="talent-card" style="padding:.45rem;margin-bottom:.9rem;display:flex;gap:.35rem;align-items:center;">
        <button type="button" wire:click="$set('activeTab','candidates')" class="talent-chip" aria-pressed="{{ $activeTab === 'candidates' ? 'true' : 'false' }}"><x-icon name="search" size="14" /> Ứng viên</button>
        <button type="button" wire:click="$set('activeTab','connections')" class="talent-chip" aria-pressed="{{ $activeTab === 'connections' ? 'true' : 'false' }}"><x-icon name="link" size="14" /> Kết nối của tôi <span style="font-size:.65rem;opacity:.7;">{{ $connections->count() }}</span></button>
        <span style="margin-left:auto;font-size:.72rem;color:var(--talent-muted);padding:.4rem .6rem;">{{ $creditSummary['reserved'] }} đang giữ chỗ · {{ $creditSummary['used'] }} đã dùng</span>
    </div>

    @if($activeTab === 'candidates')
    <div class="talent-card" style="padding:1rem;margin-bottom:1rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:.75rem;"><div><strong style="font-size:.9rem;">Bộ lọc nhanh</strong><span style="display:block;color:var(--talent-muted);font-size:.73rem;margin-top:.18rem;">Không cần viết JD. Dùng chế độ nâng cao khi bạn đã có mô tả tuyển dụng.</span></div><button type="button" class="talent-btn talent-btn-secondary" style="min-height:34px;font-size:.72rem;" x-data @click="$dispatch('toggle-advanced')"><x-icon name="filter" size="14" /> JD nâng cao</button></div>
        <form wire:submit="search" style="display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:.55rem;align-items:end;">
            <label style="font-size:.7rem;font-weight:800;color:var(--talent-muted);">Chuyên môn<select wire:model="discipline" class="talent-input" style="margin-top:.3rem;"><option value="">Tất cả</option><option>BIM</option><option>MEP</option><option>HVAC</option><option>Electrical</option><option>Plumbing</option></select></label>
            <label style="font-size:.7rem;font-weight:800;color:var(--talent-muted);">Công cụ / kỹ năng<input wire:model="skill" class="talent-input" style="margin-top:.3rem;" placeholder="Revit, Navisworks..."></label>
            <label style="font-size:.7rem;font-weight:800;color:var(--talent-muted);">Kinh nghiệm<select wire:model="minYears" class="talent-input" style="margin-top:.3rem;"><option value="0">Tất cả</option><option value="1">Từ 1 năm</option><option value="3">Từ 3 năm</option><option value="5">Từ 5 năm</option><option value="8">Từ 8 năm</option></select></label>
            <label style="font-size:.7rem;font-weight:800;color:var(--talent-muted);">Work mode<select wire:model="workMode" class="talent-input" style="margin-top:.3rem;"><option value="">Tất cả</option><option>Remote</option><option>Hybrid</option><option>On-site</option></select></label>
            <label style="font-size:.7rem;font-weight:800;color:var(--talent-muted);">Availability<select wire:model="availability" class="talent-input" style="margin-top:.3rem;"><option value="">Tất cả</option><option>Đang cập nhật</option><option>Sẵn sàng</option><option>Trong 1-3 tháng</option></select></label>
            <button class="talent-btn talent-btn-primary" type="submit"><x-icon name="search" size="15" /> Lọc ứng viên</button>
        </form>
        <div x-data="{open:false}" @toggle-advanced.window="open=!open" x-show="open" x-cloak style="margin-top:.75rem;padding-top:.75rem;border-top:1px solid var(--talent-line);"><label style="font-size:.7rem;font-weight:800;color:var(--talent-muted);">Dán mô tả tuyển dụng để hệ thống phân tích kỹ năng<input wire:model="jobDescription" class="talent-input" style="margin-top:.3rem;" placeholder="Ví dụ: BIM Coordinator biết Revit, Navisworks, tối thiểu 3 năm..."></label></div>
    </div>

    <div class="talent-results-grid">
        <div style="display:grid;gap:.7rem;align-content:start;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;"><strong style="font-size:.84rem;">{{ $candidates->count() }} hồ sơ phù hợp</strong><span style="color:var(--talent-muted);font-size:.72rem;">Sắp xếp theo điểm khớp</span></div>
            @forelse($candidates as $candidate)
                @php($connection = $connectionByEngineer->get($candidate['id']))
                <article class="talent-card talent-candidate-card" style="padding:1rem;{{ $selected && $selected['id'] === $candidate['id'] ? 'border-color:var(--talent-blue);box-shadow:0 0 0 3px rgba(31,119,190,.1);' : '' }}">
                    <button type="button" wire:click="selectCandidate({{ $candidate['id'] }})" style="display:block;width:100%;text-align:left;border:0;background:transparent;padding:0;cursor:pointer;color:inherit;">
                        <div style="display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;"><div><span style="font-size:.67rem;color:var(--talent-muted);font-weight:900;letter-spacing:.1em;">{{ $candidate['code'] }}</span><h2 style="margin:.25rem 0 .3rem;font-size:1rem;">{{ $candidate['headline'] }}</h2><p style="margin:0;color:var(--talent-muted);font-size:.75rem;">{{ $candidate['discipline'] ?: 'BIM/MEP' }} · {{ $candidate['years'] }} năm · {{ $candidate['location'] ?: 'Linh hoạt' }} · {{ $candidate['work_mode'] ?: 'Đang cập nhật' }}</p></div><span style="font-weight:900;color:var(--talent-blue);font-size:1.15rem;white-space:nowrap;">{{ $candidate['score'] }}<small style="font-size:.65rem;">%</small></span></div>
                        <p style="margin:.7rem 0;color:#365568;font-size:.8rem;line-height:1.55;">{{ $candidate['summary'] ?: 'Ứng viên đã công khai CV trên '.brand()->name.'.' }}</p>
                        <div style="display:flex;gap:.35rem;flex-wrap:wrap;">@foreach($candidate['skills'] as $skill)<span style="padding:.32rem .48rem;border-radius:999px;background:#EEF7FA;color:#236684;font-size:.67rem;font-weight:750;">{{ $skill }}</span>@endforeach</div>
                    </button>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:.7rem;margin-top:.85rem;padding-top:.7rem;border-top:1px solid #EDF2F4;"><small style="color:var(--talent-muted);font-size:.7rem;">{{ implode(' · ', $candidate['reasons']) ?: 'Khớp theo bộ lọc cơ bản' }}</small>@if($connection)<span class="talent-status" style="background:{{ $connection->status === 'accepted' ? '#EDF8F1' : '#FFF7E9' }};color:{{ $connection->status === 'accepted' ? '#18794E' : '#A36505' }};">{{ match($connection->status){'accepted'=>'Đã kết nối','rejected'=>'Đã từ chối',default=>'Đang chờ'} }}</span>@else<button type="button" wire:click="selectCandidate({{ $candidate['id'] }})" class="talent-btn talent-btn-primary" style="min-height:34px;padding:.45rem .65rem;font-size:.7rem;">Xem & gửi yêu cầu</button>@endif</div>
                </article>
            @empty
                <div class="talent-card" style="padding:2rem;text-align:center;"><span style="width:42px;height:42px;margin:auto;display:grid;place-items:center;border-radius:12px;background:#EAF5FB;color:var(--talent-blue);"><x-icon name="search" size="20" /></span><h2 style="font-size:1rem;margin:.7rem 0 .3rem;">Chưa có hồ sơ phù hợp</h2><p style="font-size:.78rem;line-height:1.55;color:var(--talent-muted);margin:0;">Thử bỏ bớt một bộ lọc, tăng phạm vi kinh nghiệm hoặc mở JD nâng cao.</p></div>
            @endforelse
        </div>

        <aside class="talent-card talent-detail-panel" style="padding:1.1rem;position:sticky;top:92px;align-self:start;">
            @if($selected)
                <div style="display:flex;justify-content:space-between;align-items:start;gap:1rem;"><div><span style="font-size:.67rem;color:var(--talent-muted);font-weight:900;letter-spacing:.1em;">{{ $selected['code'] }}</span><h2 style="font-size:1.15rem;margin:.3rem 0;">{{ $selected['headline'] }}</h2><p style="font-size:.75rem;color:var(--talent-muted);margin:0;">{{ $selected['discipline'] ?: 'BIM/MEP' }} · {{ $selected['years'] }} năm · {{ $selected['work_mode'] ?: 'Linh hoạt' }}</p></div><button type="button" wire:click="clearCandidate" aria-label="Đóng hồ sơ" style="border:0;background:#F1F6F8;color:var(--talent-muted);border-radius:8px;width:30px;height:30px;cursor:pointer;"><x-icon name="close" size="15" /></button></div>
                <div style="display:flex;gap:.4rem;flex-wrap:wrap;margin:1rem 0;">@foreach($selected['skills'] as $skill)<span style="padding:.34rem .5rem;border-radius:999px;background:#EAF5FB;color:var(--talent-blue);font-size:.68rem;font-weight:800;">{{ $skill }}</span>@endforeach</div>
                <div style="border-top:1px solid var(--talent-line);padding-top:.85rem;"><strong style="font-size:.8rem;">Kinh nghiệm</strong>@forelse($selected['experiences'] as $experience)<p style="font-size:.76rem;line-height:1.5;color:#365568;margin:.45rem 0 0;">{{ is_array($experience) ? implode(' · ', array_filter([$experience['role'] ?? '',$experience['project'] ?? '',$experience['summary'] ?? ''])) : $experience }}</p>@empty<p style="font-size:.76rem;color:var(--talent-muted);">Ứng viên chưa thêm chi tiết.</p>@endforelse</div>
                <div style="margin-top:.9rem;padding:.75rem;border-radius:10px;background:#F5FAFC;"><strong style="font-size:.78rem;display:block;">Quyền riêng tư</strong><p style="font-size:.72rem;line-height:1.5;color:var(--talent-muted);margin:.25rem 0 0;">Tên, email, số điện thoại và avatar thật chỉ mở sau khi kỹ sư chấp thuận.</p></div>
                @php($selectedConnection = $connectionByEngineer->get($selected['id']))
                @if($isAdminPreview)
                    <span style="display:block;margin-top:.8rem;padding:.7rem;border-radius:10px;background:#EAF6FB;color:#174D70;font-size:.75rem;font-weight:750;">Chế độ xem trước: yêu cầu liên hệ và credit được khóa.</span>
                @elseif(!$selectedConnection || $selectedConnection->status === 'rejected')
                    <textarea wire:model="contactMessage" class="talent-input" rows="3" style="margin-top:.8rem;resize:vertical;" placeholder="Lời nhắn ngắn cho kỹ sư (không bắt buộc)"></textarea>
                    <button type="button" wire:click="requestContact({{ $selected['id'] }})" class="talent-btn talent-btn-primary" style="width:100%;margin-top:.55rem;"><x-icon name="link" size="15" /> Gửi yêu cầu liên hệ · 1 credit</button>
                @elseif($selectedConnection->status === 'accepted')
                    <span class="talent-status" style="margin-top:.8rem;">Đã kết nối — thông tin đã được mở</span>
                @else
                    <span style="display:block;margin-top:.8rem;padding:.7rem;border-radius:10px;background:#FFF7E9;color:#8B5A00;font-size:.75rem;font-weight:750;">Yêu cầu đang chờ kỹ sư phản hồi.</span>
                @endif
            @else
                <div style="padding:2rem .5rem;text-align:center;"><span style="width:48px;height:48px;margin:auto;display:grid;place-items:center;border-radius:14px;background:#EAF5FB;color:var(--talent-blue);"><x-icon name="cv" size="22" /></span><h2 style="font-size:1rem;margin:.8rem 0 .35rem;">Chọn một hồ sơ</h2><p style="font-size:.77rem;line-height:1.55;color:var(--talent-muted);margin:0;">Thông tin chi tiết sẽ xuất hiện ở đây mà không làm mất danh sách ứng viên.</p></div>
            @endif
        </aside>
    </div>
    @error('contact')<p role="alert" style="color:#B42318;font-size:.78rem;margin-top:.7rem;">{{ $message }}</p>@enderror
    @endif

    @if($activeTab === 'connections')
        <div style="display:grid;gap:.7rem;">
            @forelse($connections as $connection)
                <article class="talent-card" style="padding:1rem;display:flex;justify-content:space-between;gap:1rem;align-items:center;flex-wrap:wrap;"><div><span style="font-size:.67rem;color:var(--talent-muted);font-weight:900;letter-spacing:.1em;">{{ $connection->cv?->data['anonymized_code'] ?? 'Hồ sơ ẩn danh' }}</span><h2 style="font-size:.95rem;margin:.25rem 0;">{{ $connection->status === 'accepted' ? ($connection->engineer->engineerProfile?->headline ?? 'Kỹ sư BIM/MEP') : 'Ứng viên ẩn danh' }}</h2>@if($connection->status === 'accepted')<p style="font-size:.75rem;color:var(--talent-muted);margin:0;">{{ data_get($connection->engineer->engineerProfile?->contact_visibility,'email') ? $connection->engineer->engineerProfile?->contact_email : 'Email ẩn theo cài đặt kỹ sư' }}</p>@else<p style="font-size:.75rem;color:var(--talent-muted);margin:0;">Thông tin sẽ mở khi kỹ sư chấp thuận.</p>@endif</div><div style="display:flex;align-items:center;gap:.55rem;">@if($connection->status === 'accepted')<span class="talent-status">Đã chấp thuận</span>@elseif($connection->status === 'rejected')<span class="talent-status" style="background:#FFF1F0;color:#B42318;">Đã từ chối</span>@else<span class="talent-status" style="background:#FFF7E9;color:#A36505;">Đang chờ</span>@endif @if($connection->conversation)<a href="{{ community_route('recruiter.messages', ['conversation' => $connection->conversation->id]) }}" class="talent-btn talent-btn-secondary" style="min-height:34px;">Mở tin nhắn</a>@endif</div></article>
            @empty
                <div class="talent-card" style="padding:2rem;text-align:center;color:var(--talent-muted);font-size:.8rem;">Bạn chưa có yêu cầu kết nối nào trong {{ brand()->name }}.</div>
            @endforelse
        </div>
    @endif
    <style>
        .talent-admin-preview{display:flex;align-items:center;justify-content:space-between;gap:.9rem;margin:0 0 1rem;padding:.8rem .9rem;border:1px solid #A8CDE1;border-radius:12px;background:#EAF6FB;color:#174D70;font-size:.78rem;line-height:1.45}.talent-admin-preview strong,.talent-admin-preview span{display:block}.talent-admin-preview strong{font-size:.84rem}.talent-admin-preview span{margin-top:.1rem}.talent-admin-preview a{color:#125A96;font-weight:800;text-decoration:none;white-space:nowrap}.talent-admin-preview a:hover{text-decoration:underline}
        .talent-results-grid{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:1rem}.talent-candidate-card{transition:border-color .18s,box-shadow .18s}.talent-candidate-card:hover{border-color:#9CC9DF}.talent-detail-panel{min-height:320px}
        @media(max-width:980px){.talent-results-grid{grid-template-columns:minmax(0,1fr) 310px}}
        @media(max-width:760px){.talent-dashboard form[style*="repeat(6"]{grid-template-columns:repeat(2,minmax(0,1fr))!important}.talent-dashboard form[style*="repeat(6"] button{grid-column:span 2}.talent-results-grid{grid-template-columns:1fr}.talent-detail-panel{position:static}.talent-card>span[style*="margin-left:auto"]{display:none}.talent-admin-preview{align-items:flex-start;flex-direction:column}}
    </style>
</section>
