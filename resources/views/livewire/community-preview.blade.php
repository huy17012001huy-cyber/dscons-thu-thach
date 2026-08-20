<div style="max-width:1000px;margin:0 auto;padding:24px 22px 60px;">
    <div class="rp-card" style="padding:0;overflow:hidden;margin-bottom:18px;">
        <div style="height:220px;position:relative;background:linear-gradient(135deg,{{ $community->theme_primary ?: '#1F77BE' }},#0e466f);">
            @if($community->banner_path)<img src="{{ asset('storage/'.$community->banner_path) }}" alt="" style="width:100%;height:100%;object-fit:cover;opacity:.48;">@endif
            <div style="position:absolute;inset:0;background:linear-gradient(0deg,rgba(4,29,47,.75),transparent 65%);"></div>
            <div style="position:absolute;left:26px;bottom:24px;display:flex;align-items:center;gap:15px;color:#fff;">
                <div style="width:70px;height:70px;border-radius:20px;background:#fff;display:grid;place-items:center;overflow:hidden;box-shadow:0 10px 24px rgba(0,0,0,.2);">
                    @if($community->logo_path)<img src="{{ asset('storage/'.$community->logo_path) }}" alt="{{ $community->name }}" style="width:100%;height:100%;object-fit:cover;">@else<span style="font-size:28px;font-weight:800;color:var(--green);">{{ strtoupper(substr($community->name,0,1)) }}</span>@endif
                </div>
                <div><h1 style="font-size:28px;margin:0 0 5px;letter-spacing:-.03em;">{{ $community->name }} @if($community->isVerified())<span style="color:#8de2ff;font-size:20px;">✓</span>@endif</h1><div style="font-size:13px;opacity:.85;">/c/{{ $community->slug }}</div></div>
            </div>
        </div>
        <div style="padding:22px 26px;display:flex;justify-content:space-between;gap:20px;align-items:flex-start;flex-wrap:wrap;">
            <div style="max-width:690px;"><div style="font-weight:700;color:var(--text);margin-bottom:6px;">{{ $community->tagline }}</div><p style="margin:0;color:var(--text-muted);line-height:1.65;">{{ $community->description ?: 'Cộng đồng học tập và chia sẻ kiến thức thực chiến.' }}</p></div>
            <div style="display:flex;gap:9px;">
                @auth
                    @if(auth()->user()->brandRoles()->where('brands.id',$community->id)->exists())
                        <a href="{{ route('community.feed', $community->slug) }}" class="ds-btn ds-btn-primary" style="text-decoration:none;">Mở community</a>
                    @else
                        <button wire:click="join" class="ds-btn ds-btn-primary">Tham gia Free</button>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="ds-btn ds-btn-primary" style="text-decoration:none;">Đăng nhập để tham gia</a>
                @endauth
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:22px;">
        <div class="rp-card" style="padding:18px;"><div style="font-size:12px;color:var(--text-muted);">Thành viên</div><div style="font-size:25px;font-weight:800;color:var(--text);margin-top:3px;">{{ number_format($memberCount) }}</div></div>
        <div class="rp-card" style="padding:18px;"><div style="font-size:12px;color:var(--text-muted);">Trạng thái</div><div style="font-size:18px;font-weight:800;color:var(--green);margin-top:5px;">{{ $community->isVerified() ? 'Đã xác minh' : 'Đang hoạt động' }}</div></div>
        <div class="rp-card" style="padding:18px;"><div style="font-size:12px;color:var(--text-muted);">Gói thành viên</div><div style="font-size:18px;font-weight:800;color:var(--text);margin-top:5px;">Free · Premium</div></div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <section class="rp-card" style="padding:20px;"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;"><h2 style="font-size:18px;margin:0;color:var(--text);">Khóa học nổi bật</h2><span style="font-size:12px;color:var(--text-muted);">Premium</span></div>@forelse($courses as $course)<div style="padding:12px 0;border-top:1px solid var(--border);"><div style="font-weight:700;color:var(--text);">{{ $course->title }}</div><div style="font-size:13px;color:var(--text-muted);margin-top:3px;">{{ $course->difficulty ?: 'Thực chiến' }} · {{ $course->modules_count ?? $course->modules()->count() }} module</div></div>@empty<div style="color:var(--text-muted);font-size:14px;">Chưa có khóa học công khai.</div>@endforelse</section>
        <section class="rp-card" style="padding:20px;"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;"><h2 style="font-size:18px;margin:0;color:var(--text);">Challenge đang mở</h2><span style="font-size:12px;color:var(--text-muted);">Premium</span></div>@forelse($challenges as $challenge)<div style="padding:12px 0;border-top:1px solid var(--border);"><div style="font-weight:700;color:var(--text);">{{ $challenge->title }}</div><div style="font-size:13px;color:var(--text-muted);margin-top:3px;">{{ $challenge->required_days }} ngày · {{ ucfirst($challenge->difficulty) }}</div></div>@empty<div style="color:var(--text-muted);font-size:14px;">Chưa có challenge công khai.</div>@endforelse</section>
    </div>
</div>
@push('styles')
<style>@media(max-width:700px){.community-preview-grid{grid-template-columns:1fr!important;}}</style>
@endpush
