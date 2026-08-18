<div>
    <h1 style="font-size:1.25rem; font-weight:800; color:#1A1A1A; margin-bottom:0.25rem;">🔍 Tìm kiếm</h1>
    <p style="font-size:0.8rem; color:#5C5C66; margin-bottom:1.5rem;">Kết quả cho "{{ $q }}"</p>

    @if(strlen($q) < 2)
    <div class="card text-center py-8">
        <p style="color:#5C5C66;">Nhập ít nhất 2 ký tự để tìm kiếm.</p>
    </div>
    @else

    {{-- Users --}}
    @if($users->count())
    <h2 style="font-size:0.85rem; font-weight:700; color:#5C5C66; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem;">Thành viên ({{ $users->count() }})</h2>
    <div class="card mb-4">
        @foreach($users as $user)
        <div class="flex items-center gap-3 py-2 {{ !$loop->last ? 'border-b' : '' }}" style="{{ !$loop->last ? 'border-color:#E1E1E1;' : '' }}">
            <img src="{{ $user->avatar_url }}" class="avatar w-9 h-9" alt="">
            <div style="flex:1;">
                <a href="{{ route('profile', $user->username ?? $user->id) }}" style="font-size:0.85rem; font-weight:600; color:#1A1A1A;">{{ $user->name }}</a>
                <p style="font-size:0.7rem; color:#5C5C66;">@{{ $user->username }} · {{ $user->job_stage }} · Lv.{{ $user->level }}</p>
            </div>
            <span class="badge badge-class-{{ $user->class_color }}" style="font-size:0.65rem;">{{ $user->class_emoji }}</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Posts --}}
    @if($posts->count())
    <h2 style="font-size:0.85rem; font-weight:700; color:#5C5C66; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem;">Bài viết ({{ $posts->count() }})</h2>
    <div class="flex flex-col gap-2 mb-4">
        @foreach($posts as $post)
        <div class="card">
            <div class="flex items-center gap-2 mb-2">
                <img src="{{ $post->user->avatar_url }}" class="avatar w-7 h-7" alt="">
                <span style="font-size:0.8rem; font-weight:600; color:#1A1A1A;">{{ $post->user->name }}</span>
                <span class="badge badge-pillar-{{ $post->pillar }}" style="font-size:0.6rem;">{{ $post->pillar_label }}</span>
                <span style="font-size:0.7rem; color:#5C5C66;">{{ $post->created_at->diffForHumans() }}</span>
            </div>
            @if($post->title)
            <h3 style="font-size:0.9rem; font-weight:700; color:#1A1A1A; margin-bottom:0.25rem;">{{ $post->title }}</h3>
            @endif
            <p style="font-size:0.8rem; color:#2E2E2E; line-height:1.5;">{{ Str::limit($post->content, 200) }}</p>
            <div class="flex gap-3 mt-2" style="font-size:0.7rem; color:#5C5C66;">
                <span>♥ {{ $post->likes_count }}</span>
                <span>💬 {{ $post->all_comments_count }}</span>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Questions --}}
    @if($questions->count())
    <h2 style="font-size:0.85rem; font-weight:700; color:#5C5C66; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem;">Câu hỏi ({{ $questions->count() }})</h2>
    <div class="flex flex-col gap-2 mb-4">
        @foreach($questions as $question)
        <div class="card">
            <div class="flex items-center gap-2 mb-1">
                <span style="font-size:0.8rem; font-weight:600; color:#1A1A1A;">{{ $question->title }}</span>
                <span class="badge" style="font-size:0.6rem; background:#EEECE9; color:#5C5C66;">{{ $question->answers_count }} câu trả lời</span>
            </div>
            @if($question->body)
            <p style="font-size:0.75rem; color:#5C5C66; line-height:1.4;">{{ Str::limit($question->body, 150) }}</p>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    @if($posts->isEmpty() && $users->isEmpty() && $questions->isEmpty())
    <div class="card text-center py-8">
        <p style="font-size:1.5rem; margin-bottom:0.5rem;">🔍</p>
        <p style="color:#5C5C66;">Không tìm thấy kết quả cho "{{ $q }}"</p>
    </div>
    @endif

    @endif
</div>
