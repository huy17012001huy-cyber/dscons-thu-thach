<div class="challenge-lesson-page">
    <style>
        .challenge-lesson-page{--blue:#1F77BE;--navy:#123B59;--muted:#61798A;--line:#D5E5ED;max-width:860px;margin:0 auto;padding-bottom:32px}
        .lesson-back{display:inline-flex;align-items:center;gap:7px;color:var(--muted);font-size:.78rem;text-decoration:none;margin:2px 0 14px}
        .lesson-back:hover{color:var(--blue)}
        .lesson-hero{position:relative;overflow:hidden;border:1px solid #BBD9E8;border-radius:22px;padding:28px 30px;background:linear-gradient(135deg,#EAF6FB 0%,#fff 72%);box-shadow:0 14px 36px rgba(18,59,89,.08)}
        .lesson-hero:after{content:"";position:absolute;width:180px;height:180px;border-radius:50%;right:-70px;top:-90px;background:rgba(31,119,190,.12)}
        .lesson-kicker{position:relative;z-index:1;display:flex;align-items:center;gap:8px;color:var(--blue);font-weight:800;font-size:.72rem;letter-spacing:.07em;text-transform:uppercase}
        .lesson-hero h1{position:relative;z-index:1;margin:12px 0 9px;color:var(--navy);font-size:clamp(1.5rem,3vw,2.15rem);line-height:1.15;letter-spacing:-.04em}
        .lesson-hero p{position:relative;z-index:1;max-width:680px;margin:0;color:#456477;font-size:.92rem;line-height:1.65}
        .lesson-meta{position:relative;z-index:1;display:flex;flex-wrap:wrap;gap:7px;margin-top:18px}.lesson-chip{display:inline-flex;align-items:center;min-height:28px;padding:0 10px;border:1px solid #BBD9E8;border-radius:999px;color:#125A96;background:#fff;font-size:.7rem;font-weight:800}
        .lesson-body{margin-top:16px;border:1px solid var(--line);border-radius:18px;background:#fff;box-shadow:0 6px 20px rgba(18,59,89,.05)}
        .lesson-section{padding:22px 26px;border-bottom:1px solid #E5EFF4}.lesson-section:last-child{border-bottom:0}.lesson-section h2{display:flex;align-items:center;gap:9px;margin:0 0 10px;color:var(--navy);font-size:1rem}.lesson-section h2:before{content:"";width:7px;height:7px;border-radius:50%;background:var(--blue);box-shadow:0 0 0 5px #EAF6FB}.lesson-section p,.lesson-section li{color:#456477;font-size:.86rem;line-height:1.7}.lesson-section ul,.lesson-section ol{margin:7px 0 0;padding-left:1.25rem}.lesson-section li+li{margin-top:5px}
        .lesson-callout{border:1px solid #BBD9E8;border-left:4px solid var(--blue);border-radius:12px;padding:13px 15px;background:#F3FAFD;color:#456477;font-size:.84rem;line-height:1.65}.lesson-callout.is-warn{border-color:#F1D594;border-left-color:#F39402;background:#FFF9EC}.lesson-callout strong{color:var(--navy)}
        .lesson-prompt{overflow:hidden;border-radius:13px;background:#102F47}.lesson-prompt-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:11px 14px;color:#fff;font-size:.78rem}.lesson-prompt button{min-height:34px;padding:0 10px;border:1px solid rgba(255,255,255,.45);border-radius:8px;color:#fff;background:transparent;cursor:pointer;font-size:.72rem}.lesson-prompt pre{max-height:400px;overflow:auto;margin:0;padding:15px;color:#E4F6FF;font: .74rem/1.65 ui-monospace,SFMono-Regular,Consolas,monospace;white-space:pre-wrap}
        .lesson-nav{display:flex;justify-content:space-between;gap:12px;margin-top:16px}.lesson-nav a{display:flex;flex-direction:column;gap:3px;min-width:0;padding:13px 15px;border:1px solid var(--line);border-radius:12px;color:var(--navy);background:#fff;text-decoration:none}.lesson-nav a:hover{border-color:#8DBDD5;box-shadow:0 7px 18px rgba(18,59,89,.08)}.lesson-nav small{color:var(--muted);font-size:.68rem}.lesson-nav strong{font-size:.78rem}.lesson-nav .next{text-align:right;margin-left:auto}
        @media(max-width:640px){.lesson-hero{padding:22px 18px;border-radius:17px}.lesson-body{border-radius:14px}.lesson-section{padding:18px 16px}.lesson-nav{flex-direction:column}.lesson-nav .next{margin-left:0;text-align:left}}
        @media(prefers-reduced-motion:reduce){.lesson-nav a{transition:none}}
    </style>

    <a class="lesson-back" href="{{ community_route('challenge.show', ['slug' => $expedition->slug]) }}">← Quay lại lộ trình 21 ngày</a>
    <header class="lesson-hero">
        <div class="lesson-kicker">Ngày {{ $task->day_number }} · {{ $payload['modality'] === 'live' ? 'Livestream' : 'Tự thực hành' }}</div>
        <h1>{{ $task->title }}</h1>
        <p>{{ $task->description }}</p>
        <div class="lesson-meta"><span class="lesson-chip">{{ $payload['estimated_minutes'] ?? 0 }} phút</span><span class="lesson-chip">Hướng dẫn chuyên sâu</span><span class="lesson-chip">Đạt từ {{ $payload['pass_score'] ?? 70 }}/100</span></div>
    </header>

    <main class="lesson-body">
        <section class="lesson-section"><h2>Hôm nay bạn sẽ làm gì?</h2><p>{{ $task->description }}</p><div class="lesson-callout"><strong>Kết quả bắt buộc:</strong> {{ $payload['required_outcome'] ?? 'Hoàn thành đúng đầu ra của bài học.' }}</div></section>
        <section class="lesson-section"><h2>Mục tiêu của bài</h2><ul>@foreach($payload['learning_objectives'] ?? [] as $item)<li>{{ $item }}</li>@endforeach</ul></section>
        <section class="lesson-section"><h2>Chuẩn bị trước khi bắt đầu</h2><ul>@foreach($payload['prerequisites'] ?? [] as $item)<li>{{ $item }}</li>@endforeach</ul></section>
        <section class="lesson-section"><h2>Trong lúc thực hiện</h2><ul>@foreach($payload['ai_actions'] ?? [] as $item)<li>{{ $item }}</li>@endforeach</ul></section>
        <section class="lesson-section"><h2>SOP từng bước</h2><ol>@foreach($payload['sop_steps'] ?? [] as $item)<li>{{ $item }}</li>@endforeach</ol></section>
        @if(!empty($payload['ai_prompt']))
        <section class="lesson-section"><h2>Prompt copy vào AI Agent</h2><div class="lesson-prompt" x-data="{copied:false}"><div class="lesson-prompt-head"><strong>Prompt ngày {{ $task->day_number }}</strong><button type="button" @click="navigator.clipboard.writeText($refs.prompt.innerText);copied=true;setTimeout(()=>copied=false,1600)"><span x-show="!copied">Sao chép prompt</span><span x-show="copied" x-cloak>Đã sao chép</span></button></div><pre x-ref="prompt">{{ $payload['ai_prompt'] }}</pre></div></section>
        @endif
        <section class="lesson-section"><h2>Bạn cần tự kiểm tra</h2><ul>@foreach($payload['verification_checklist'] ?? [] as $item)<li>{{ $item }}</li>@endforeach</ul></section>
        @if(!empty($payload['feed_activity']))<section class="lesson-section"><h2>Chia sẻ cùng cộng đồng</h2><div class="lesson-callout">{{ $payload['feed_activity'] }}</div></section>@endif
        <section class="lesson-section"><h2>Bài tập và bằng chứng cần nộp</h2><div class="lesson-callout"><strong>{{ $payload['homework']['title'] ?? 'Bài thực hành hôm nay' }}</strong></div><ul>@foreach($payload['homework']['instructions'] ?? $payload['evidence_requirements'] ?? [] as $item)<li>{{ $item }}</li>@endforeach</ul></section>
        <section class="lesson-section"><h2>Cách chấm</h2><ul>@foreach($payload['rubric'] ?? [] as $criterion)<li><strong>{{ $criterion['points'] ?? 0 }} điểm:</strong> {{ $criterion['label'] ?? '' }}</li>@endforeach</ul><div class="lesson-callout is-warn" style="margin-top:12px"><strong>Chưa đạt khi:</strong> chỉ nộp ảnh code, không có kết quả chạy thật, sửa model gốc hoặc không thể đối chiếu/cài lại.</div></section>
        @if(!empty($payload['common_errors']))<section class="lesson-section"><h2>Lỗi thường gặp</h2><ul>@foreach($payload['common_errors'] as $item)<li>{{ $item }}</li>@endforeach</ul></section>@endif
    </main>
    <nav class="lesson-nav" aria-label="Điều hướng bài học">
        @if($previous)<a href="{{ community_route('challenge.lesson', ['slug'=>$expedition->slug,'day'=>$previous->day_number]) }}"><small>← Bài trước</small><strong>Ngày {{ $previous->day_number }} · {{ $previous->title }}</strong></a>@else <span></span>@endif
        @if($next)<a class="next" href="{{ community_route('challenge.lesson', ['slug'=>$expedition->slug,'day'=>$next->day_number]) }}"><small>Bài tiếp theo →</small><strong>Ngày {{ $next->day_number }} · {{ $next->title }}</strong></a>@endif
    </nav>
</div>
