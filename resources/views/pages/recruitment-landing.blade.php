@extends('layouts.recruiter')

@section('content')
<div class="talent-landing">
    <section class="talent-card" style="padding:clamp(1.4rem,4vw,3.5rem);overflow:hidden;position:relative;background:linear-gradient(120deg,#fff 0%,#F2F9FC 72%,#E5F3F9 100%);">
        <div style="position:absolute;right:-80px;top:-100px;width:300px;height:300px;border:1px solid #BBDCEB;border-radius:50%;opacity:.7;"></div>
        <div style="position:absolute;right:40px;top:45px;width:130px;height:130px;border:1px solid #BBDCEB;border-radius:50%;opacity:.6;"></div>
        <div style="position:relative;max-width:780px;">
            <div class="talent-kicker">{{ brand()->name }} · Talent marketplace</div>
            <h1 class="talent-heading" style="font-size:clamp(2.25rem,6vw,4.6rem);max-width:760px;">Tìm đúng kỹ sư cho dự án tiếp theo.</h1>
            <p class="talent-subtitle" style="font-size:1rem;max-width:650px;margin:1.1rem 0 0;">Lọc ứng viên BIM/MEP theo kỹ năng và kinh nghiệm thực tế. Hồ sơ được ẩn danh để kỹ sư luôn chủ động: chỉ mở liên hệ sau khi họ đồng ý.</p>
            <div style="display:flex;flex-wrap:wrap;gap:.7rem;margin-top:1.5rem;">
                @auth
                    @if(auth()->user()->isRecruiter())
                        <a href="{{ community_route('recruiter.onboarding') }}" class="talent-btn talent-btn-primary"><x-icon name="search" size="16" /> Bắt đầu tìm ứng viên</a>
                    @elseif(auth()->user()->is_admin)
                        <a href="{{ community_route('manage.recruitment') }}" class="talent-btn talent-btn-primary"><x-icon name="settings" size="16" /> Quản trị Talent</a>
                    @else
                        <a href="{{ community_route('engineer.cv') }}" class="talent-btn talent-btn-primary"><x-icon name="cv" size="16" /> Tạo CV kỹ sư</a>
                    @endif
                @else
                    <a href="{{ route('login') }}?redirect={{ urlencode(community_route('recruiter.onboarding')) }}" class="talent-btn talent-btn-primary"><x-icon name="briefcase" size="16" /> Tôi là nhà tuyển dụng</a>
                    @if(brand()->has_cv)<a href="{{ route('login') }}?redirect={{ urlencode(community_route('engineer.cv')) }}" class="talent-btn talent-btn-secondary">Tôi là kỹ sư</a>@endif
                @endauth
            </div>
        </div>
    </section>

    <section style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.85rem;margin-top:1rem;">
        @foreach([
            ['search','Lọc nhanh','Chọn chuyên môn, công cụ, kinh nghiệm và work mode.'],
            ['lock','Riêng tư trước','Không tên, email, điện thoại hay avatar thật trước khi chấp thuận.'],
            ['check','Kết nối có kiểm soát','Credit chỉ được dùng khi kỹ sư đồng ý mở liên hệ.'],
        ] as [$icon,$title,$copy])
        <article class="talent-card" style="padding:1.1rem;">
            <span style="width:38px;height:38px;border-radius:11px;background:#EAF5FB;color:var(--talent-blue);display:grid;place-items:center;"><x-icon name="{{ $icon }}" size="19" /></span>
            <h2 style="font-size:.95rem;margin:.8rem 0 .35rem;">{{ $title }}</h2>
            <p style="font-size:.8rem;line-height:1.55;color:var(--talent-muted);margin:0;">{{ $copy }}</p>
        </article>
        @endforeach
    </section>

    <section style="display:grid;grid-template-columns:1.1fr .9fr;gap:1rem;margin-top:1rem;align-items:stretch;">
        <div class="talent-card" style="padding:1.2rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;"><div><div class="talent-kicker">Quy trình tuyển dụng</div><h2 style="margin:.3rem 0 0;font-size:1.25rem;">Chỉ ba bước để bắt đầu</h2></div><span class="talent-status"><x-icon name="shield" size="13" /> Privacy-first</span></div>
            <div style="display:grid;gap:.85rem;margin-top:1.2rem;">
                @foreach([['01','Chọn tiêu chí','Không cần viết JD: chọn nhanh kỹ năng và level bạn cần.'],['02','Xem hồ sơ ẩn danh','So sánh kinh nghiệm, dự án và lý do khớp trên một màn hình.'],['03','Gửi yêu cầu','Kỹ sư nhận thông báo và chỉ mở liên hệ khi họ đồng ý.']] as [$number,$title,$copy])
                <div style="display:grid;grid-template-columns:38px 1fr;gap:.7rem;align-items:start;"><span style="width:34px;height:34px;border-radius:9px;background:var(--talent-ink);color:#fff;display:grid;place-items:center;font-size:.72rem;font-weight:900;">{{ $number }}</span><div><strong style="font-size:.86rem;">{{ $title }}</strong><p style="font-size:.78rem;color:var(--talent-muted);line-height:1.5;margin:.2rem 0 0;">{{ $copy }}</p></div></div>
                @endforeach
            </div>
        </div>
        <div class="talent-card" style="padding:1.2rem;background:var(--talent-ink);color:#fff;">
            <div class="talent-kicker" style="color:#FFC15C;">Hồ sơ mẫu ẩn danh</div>
            <div style="margin-top:1.2rem;padding-bottom:.9rem;border-bottom:1px solid rgba(255,255,255,.16);"><span style="font-size:.68rem;color:#A9C6D5;font-weight:800;letter-spacing:.1em;">BIM-0248</span><h3 style="margin:.3rem 0;font-size:1.15rem;">BIM Coordinator · MEP</h3><p style="color:#BCD2DD;font-size:.78rem;margin:0;">5 năm · Hà Nội · Hybrid</p></div>
            <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-top:1rem;">@foreach(['Revit','Navisworks','HVAC','Clash detection'] as $skill)<span style="padding:.35rem .5rem;border:1px solid rgba(255,255,255,.2);border-radius:999px;font-size:.68rem;color:#DCECF3;">{{ $skill }}</span>@endforeach</div>
            <p style="font-size:.78rem;line-height:1.6;color:#BCD2DD;margin:1.1rem 0 0;">Bạn chỉ nhìn thấy năng lực cần thiết cho quyết định tuyển dụng. Danh tính thuộc về kỹ sư.</p>
        </div>
    </section>
</div>
<style>
@media(max-width:760px){.talent-landing section[style*="repeat(3"]{grid-template-columns:1fr}.talent-landing section[style*="1.1fr"]{grid-template-columns:1fr}}
</style>
@endsection
