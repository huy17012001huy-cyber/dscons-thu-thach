<div class="page-shell" style="max-width:1100px;margin:0 auto;padding:28px 24px 64px;">
    <div style="display:flex;justify-content:space-between;gap:20px;align-items:flex-end;flex-wrap:wrap;margin-bottom:24px;">
        <div>
            <div style="font-size:12px;text-transform:uppercase;letter-spacing:.14em;font-weight:800;color:var(--green);">DSCons network</div>
            <h1 style="margin:6px 0 5px;font-size:30px;letter-spacing:-.03em;color:var(--text);">Khám phá cộng đồng</h1>
            <p style="margin:0;color:var(--text-muted);max-width:620px;line-height:1.6;">Tìm một không gian học tập phù hợp với mục tiêu nghề nghiệp và tham gia cùng những người đang xây dựng năng lực thực chiến.</p>
        </div>
        @auth
            <a href="{{ route('community.create') }}" class="ds-btn ds-btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:8px;">Tạo cộng đồng</a>
        @endauth
    </div>

    <div style="position:relative;margin-bottom:22px;">
        <svg aria-hidden="true" viewBox="0 0 24 24" style="position:absolute;left:14px;top:13px;width:18px;color:var(--text-muted);"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="m16 16 5 5" fill="none" stroke="currentColor" stroke-width="2"/></svg>
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Tìm theo tên hoặc chủ đề…" aria-label="Tìm cộng đồng" style="width:100%;box-sizing:border-box;padding:12px 15px 12px 42px;border:1px solid var(--border);border-radius:12px;background:#fff;color:var(--text);outline:none;">
    </div>

    @if($communities->isEmpty())
        <div class="rp-card" style="padding:42px;text-align:center;color:var(--text-muted);">Chưa có cộng đồng phù hợp. Hãy thử từ khóa khác.</div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;">
            @foreach($communities as $community)
                <article class="rp-card" style="padding:0;overflow:hidden;display:flex;flex-direction:column;">
                    <div style="height:118px;background:linear-gradient(130deg,{{ $community->theme_primary ?: '#1F77BE' }},#0f527f);position:relative;">
                        @if($community->banner_path)<img src="{{ asset('storage/'.$community->banner_path) }}" alt="" style="width:100%;height:100%;object-fit:cover;opacity:.45;">@endif
                        <div style="position:absolute;inset:0;background:linear-gradient(0deg,rgba(7,35,54,.48),transparent 70%);"></div>
                        <div style="position:absolute;left:18px;bottom:-22px;width:52px;height:52px;border-radius:15px;background:#fff;border:3px solid #fff;box-shadow:0 5px 16px rgba(15,62,92,.18);display:grid;place-items:center;overflow:hidden;">
                            @if($community->logo_path)<img src="{{ asset('storage/'.$community->logo_path) }}" alt="" style="width:100%;height:100%;object-fit:cover;">@else<span style="font-weight:800;font-size:19px;color:var(--green);">{{ strtoupper(substr($community->name,0,1)) }}</span>@endif
                        </div>
                    </div>
                    <div style="padding:30px 18px 18px;display:flex;flex-direction:column;gap:8px;flex:1;">
                        <div style="display:flex;gap:7px;align-items:center;">
                            <h2 style="font-size:18px;margin:0;color:var(--text);">{{ $community->name }}</h2>
                            @if($community->isVerified())<span title="Đã xác minh" style="color:var(--green);font-size:14px;">✓</span>@endif
                        </div>
                        <div style="font-size:13px;color:var(--text-muted);">/c/{{ $community->slug }} · {{ number_format($community->users_count) }} thành viên</div>
                        <p style="font-size:14px;line-height:1.55;color:var(--text-muted);margin:2px 0 12px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">{{ $community->description ?: ($community->tagline ?: 'Một cộng đồng học tập thực chiến trên DSCons.') }}</p>
                        <div style="display:flex;gap:9px;margin-top:auto;">
                            <a href="{{ route('community.preview', $community->slug) }}" class="ds-btn" style="flex:1;text-align:center;text-decoration:none;">Xem community</a>
                            @auth
                                @if($joinedIds->contains($community->id))
                                    <a href="{{ route('community.feed', $community->slug) }}" class="ds-btn ds-btn-primary" style="flex:1;text-align:center;text-decoration:none;">Mở bảng tin</a>
                                @else
                                    <button wire:click="join({{ $community->id }})" class="ds-btn ds-btn-primary" style="flex:1;">Tham gia Free</button>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="ds-btn ds-btn-primary" style="flex:1;text-align:center;text-decoration:none;">Đăng nhập để tham gia</a>
                            @endauth
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
