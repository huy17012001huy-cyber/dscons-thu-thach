<div x-data="{ openLesson: @entangle('openLessonId') }">
    <div class="mb-4">
        <a href="{{ route('academy') }}" style="font-size:0.8rem; color:#5C5C66;">← Quay lại Khóa học</a>
    </div>

    {{-- Course header --}}
    <div class="card mb-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="badge badge-pillar-{{ $course->pillar }}" style="font-size:0.7rem;">{{ ucfirst($course->pillar) }}</span>
            <span class="badge" style="font-size:0.7rem; {{ match($course->difficulty) { 'basic' => 'background:#D1FAE5; color:#065F46;', 'advanced' => 'background:#E8F5E9; color:#1B5E20;', 'expert' => 'background:#FEE2E2; color:#991B1B;', default => 'background:#EEECE9; color:#5C5C66;' } }}">{{ ucfirst($course->difficulty) }}</span>
            @if($course->min_level > 1)
            <span class="level-badge">Lv.{{ $course->min_level }}+</span>
            @endif
        </div>

        <h1 style="font-size:1.25rem; font-weight:800; color:#1A1A1A; margin-bottom:0.5rem;">{{ $course->title }}</h1>

        @if($course->description)
        <p style="font-size:0.875rem; color:#2E2E2E; line-height:1.6; margin-bottom:1rem;">{{ $course->description }}</p>
        @endif

        <div class="flex flex-wrap gap-4 mb-4">
            <div>
                <p style="font-size:0.7rem; color:#5C5C66;">Phần thưởng</p>
                <p style="font-size:0.9rem; font-weight:700; color:#d17856; margin-top:0.25rem;">+{{ $course->xp_reward }} XP</p>
            </div>
            <div>
                <p style="font-size:0.7rem; color:#5C5C66;">Tiến độ</p>
                <p style="font-size:0.9rem; font-weight:700; color:#1A1A1A; margin-top:0.25rem;">{{ $completedLessons }}/{{ $totalLessons }} bài</p>
            </div>
        </div>

        @if($totalLessons > 0 && $enrolled)
        <div class="xp-bar mb-3" style="height:6px;">
            <div class="xp-bar-fill" style="width:{{ $totalLessons > 0 ? round($completedLessons / $totalLessons * 100) : 0 }}%;"></div>
        </div>
        @endif

        @if($enrolled)
        <p style="font-size:0.8rem; color:#059669; font-weight:600;">✓ Đã đăng ký</p>
        @elseif($pendingPayment)
        @php
            $transferCode = 'COURSE' . $course->id . 'U' . auth()->id();
            $bankAccount = config('services.sepay.bank_account');
            $bankName = config('services.sepay.bank_name');
            $qrUrl = $bankAccount
                ? 'https://qr.sepay.vn/img?' . http_build_query(['acc' => $bankAccount, 'bank' => $bankName, 'amount' => $course->price, 'des' => $transferCode, 'template' => 'compact'])
                : null;
        @endphp
        <div wire:poll.5s style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:0.5rem; padding:0.75rem; margin-top:0.5rem;">
            <p style="font-size:0.8rem; font-weight:600; color:#92400E; margin-bottom:0.375rem;">⏳ Chờ thanh toán</p>

            @if($qrUrl)
            <div class="text-center" style="margin-bottom:0.75rem;">
                <img src="{{ $qrUrl }}" alt="QR Thanh toán" style="max-width:220px; margin:0 auto; border-radius:0.5rem;">
                <p style="font-size:0.7rem; color:#5C5C66; margin-top:0.375rem;">Quét mã QR bằng app ngân hàng</p>
            </div>
            @endif

            <div style="background:#FFFFFF; border:1px solid #E1E1E1; border-radius:0.375rem; padding:0.625rem; margin-bottom:0.375rem;">
                <div class="flex justify-between items-center" style="margin-bottom:0.25rem;">
                    <span style="font-size:0.75rem; color:#5C5C66;">Nội dung CK:</span>
                    <strong style="font-size:0.85rem; color:#d17856; letter-spacing:0.05em;">{{ $transferCode }}</strong>
                </div>
                <div class="flex justify-between items-center">
                    <span style="font-size:0.75rem; color:#5C5C66;">Số tiền:</span>
                    <strong style="font-size:0.85rem; color:#1A1A1A;">{{ number_format($course->price, 0, ',', '.') }}đ</strong>
                </div>
            </div>

            <p style="font-size:0.7rem; color:#059669; font-weight:500;">⚡ Trang sẽ tự cập nhật khi nhận được tiền (1-3 phút)</p>
        </div>
        @else
        @auth
        <div class="flex items-center gap-3 mt-2">
            @if($course->price > 0)
            <p style="font-size:1rem; font-weight:800; color:#d17856;">{{ number_format($course->price, 0, ',', '.') }}đ</p>
            @else
            <p style="font-size:0.85rem; font-weight:600; color:#059669;">Miễn phí</p>
            @endif
            <button wire:click="enroll" wire:loading.attr="disabled" wire:loading.class="opacity-50" wire:target="enroll" class="btn btn-primary">
                {{ $course->price > 0 ? 'Mua khóa học' : 'Đăng ký khóa học' }}
            </button>
        </div>
        @endauth
        @endif

        <a href="{{ community_route('events', ['courseFilter' => $course->id]) }}" class="event-related" style="display:inline-flex;align-items:center;gap:.35rem;margin-top:.75rem;color:#1F77BE;font-size:.78rem;font-weight:700;text-decoration:none;">
            Xem sự kiện liên quan →
        </a>
    </div>

    @if($premiumLocked && !$enrolled)
    <div class="card" style="margin-bottom:1rem;padding:1.25rem;border-color:#B8D7E6;background:linear-gradient(135deg,#F7FCFD,#fff);">
        <div style="display:flex;align-items:flex-start;gap:12px;"><div style="width:38px;height:38px;border-radius:11px;background:#E1F4F7;color:#1F77BE;display:grid;place-items:center;font-weight:800;">★</div><div><strong style="display:block;color:#123B59;font-size:1rem;">Nội dung Premium</strong><p style="margin:.3rem 0 0;color:#61798A;font-size:.85rem;line-height:1.55;">Nâng hạng membership để mở khóa toàn bộ bài học, bài tập và các sự kiện liên quan.</p><a href="{{ community_route('membership') }}" class="btn btn-primary" style="display:inline-flex;margin-top:.75rem;text-decoration:none;">Xem gói Premium</a></div></div>
    </div>
    @endif

    @if(!$premiumLocked || $enrolled)
    {{-- Main content: Video left + Lesson list right --}}
    <div class="flex gap-4" style="align-items:flex-start;">

        {{-- LEFT: Video player + lesson content --}}
        <div style="flex:1; min-width:0;">
            @php
                $activeLesson = $openLessonId
                    ? $course->modules->flatMap(fn($m) => $m->lessons)->firstWhere('id', $openLessonId)
                    : null;
            @endphp

            @if($activeLesson && in_array($activeLesson->id, $unlockedIds) && $enrolled)
                {{-- Video embed --}}
                @if($activeLesson->video_url)
                @php
                    $ytId = null;
                    if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([a-zA-Z0-9_-]{11})/', $activeLesson->video_url, $m)) {
                        $ytId = $m[1];
                    }
                @endphp
                @if($ytId)
                <div style="position:relative; padding-bottom:56.25%; height:0; overflow:hidden; border-radius:0.75rem; margin-bottom:1rem; background:#000;">
                    <iframe src="https://www.youtube.com/embed/{{ $ytId }}" style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
                @else
                <div class="card mb-4" style="padding:1rem;">
                    <a href="{{ $activeLesson->video_url }}" target="_blank" rel="noopener" class="flex items-center gap-2" style="font-size:0.85rem; color:#d17856; font-weight:600;">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.84A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.27l9.344-5.891a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                        Xem video bài giảng
                    </a>
                </div>
                @endif
                @else
                <div style="padding-bottom:56.25%; background:#F7F5F3; border-radius:0.75rem; margin-bottom:1rem; display:flex; align-items:center; justify-content:center; position:relative;">
                    <p style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); color:#5C5C66; font-size:0.85rem;">Bài học này chưa có video</p>
                </div>
                @endif

                {{-- Lesson title + content --}}
                <div class="card mb-4">
                    <h2 style="font-size:1rem; font-weight:700; color:#1A1A1A; margin-bottom:0.5rem;">{{ $activeLesson->title }}</h2>
                    @if($activeLesson->description)
                    <p style="font-size:0.85rem; color:#5C5C66; margin-bottom:0.75rem;">{{ $activeLesson->description }}</p>
                    @endif
                    @if($activeLesson->content)
                    <div style="font-size:0.85rem; color:#2E2E2E; line-height:1.7; white-space:pre-line;">{{ $activeLesson->content }}</div>
                    @endif

                    {{-- Tasks --}}
                    @if($activeLesson->tasks->count() > 0)
                    <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid #E1E1E1;">
                        <h3 style="font-size:0.85rem; font-weight:700; color:#1A1A1A; margin-bottom:0.75rem;">Bài tập</h3>
                        @php $prevTaskSubmitted = true; @endphp
                        @foreach($activeLesson->tasks as $task)
                        @php
                            $taskSubmission = $submissions[$task->id] ?? null;
                            $taskIsSubmitted = $taskSubmission !== null;
                            $taskIsUnlocked = $prevTaskSubmitted;
                        @endphp
                        <div style="padding:0.75rem; border-radius:0.5rem; margin-bottom:0.5rem;
                            {{ $taskIsSubmitted ? 'background:#F0FDF4; border:1px solid #A7F3D0;' : ($taskIsUnlocked ? 'background:#FFFBEB; border:1px solid #FDE68A;' : 'background:#F7F5F3; border:1px solid #E1E1E1;') }}">
                            <div class="flex items-start gap-2">
                                @if($taskIsSubmitted)
                                <span style="color:#059669; flex-shrink:0;">✓</span>
                                @elseif($taskIsUnlocked)
                                <span style="color:#d17856; flex-shrink:0;">●</span>
                                @else
                                <span style="color:#C8C8C8; flex-shrink:0;">▪</span>
                                @endif
                                <div style="flex:1;">
                                    <p style="font-size:0.8rem; font-weight:600; color:{{ $taskIsUnlocked || $taskIsSubmitted ? '#1A1A1A' : '#A8A8B0' }};">{{ $task->title }}</p>
                                    @if($task->description && ($taskIsUnlocked || $taskIsSubmitted))
                                    <p style="font-size:0.75rem; color:#5C5C66; margin-top:0.25rem; line-height:1.5;">{{ $task->description }}</p>
                                    @endif
                                    @if($taskIsSubmitted)
                                    <div style="margin-top:0.5rem; padding:0.5rem; background:#FFF; border-radius:0.375rem; border:1px solid #E1E1E1;">
                                        <p style="font-size:0.75rem; color:#2E2E2E; line-height:1.5; white-space:pre-line;">{{ $taskSubmission->content }}</p>
                                        <p style="font-size:0.65rem; color:#5C5C66; margin-top:0.375rem;">
                                            {{ $taskSubmission->submitted_at->format('H:i d/m') }}
                                            · <span style="color:{{ $taskSubmission->status === 'approved' ? '#059669' : ($taskSubmission->status === 'rejected' ? '#991B1B' : '#d17856') }};">{{ match($taskSubmission->status) { 'approved' => 'Đã duyệt', 'rejected' => 'Cần sửa', default => 'Chờ duyệt' } }}</span>
                                        </p>
                                    </div>
                                    @elseif($taskIsUnlocked)
                                        @if($activeTaskId === $task->id)
                                        <div style="margin-top:0.5rem;">
                                            <textarea wire:model="taskAnswer" class="input" rows="3" placeholder="Nhập câu trả lời..."
                                                x-data x-init="$el.style.height = $el.scrollHeight + 'px'"
                                                @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                                style="overflow:hidden; resize:none; font-size:0.8rem;"></textarea>
                                            @error('taskAnswer') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
                                            <div class="flex gap-2 mt-2">
                                                <button wire:click="submitTask" class="btn btn-primary" style="font-size:0.75rem; padding:0.3rem 0.75rem;">Gửi bài</button>
                                                <button wire:click="startTask(null)" class="btn btn-ghost" style="font-size:0.75rem; padding:0.3rem 0.75rem;">Hủy</button>
                                            </div>
                                        </div>
                                        @else
                                        <button wire:click="startTask({{ $task->id }})" style="margin-top:0.5rem; font-size:0.75rem; color:#d17856; font-weight:600; cursor:pointer;">Trả lời →</button>
                                        @endif
                                    @else
                                    <p style="font-size:0.7rem; color:#A8A8B0; margin-top:0.25rem;">Trả lời câu trước để mở khoá</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @php $prevTaskSubmitted = $taskIsSubmitted; @endphp
                        @endforeach
                    </div>
                    @endif

                    {{-- Complete button --}}
                    @if($activeLesson->tasks->count() === 0 && !in_array($activeLesson->id, $completedIds))
                    <button wire:click="completeLesson({{ $activeLesson->id }})" class="btn btn-primary mt-3" style="font-size:0.8rem;">Hoàn thành bài học</button>
                    @endif
                </div>
            @else
                {{-- No lesson selected --}}
                <div class="card text-center" style="padding:3rem 1rem;">
                    <p style="font-size:2rem; margin-bottom:0.5rem;">▦</p>
                    <p style="font-size:0.9rem; color:#5C5C66;">Chọn bài học bên phải để bắt đầu</p>
                </div>
            @endif
        </div>

        {{-- RIGHT: Lesson list --}}
        <div class="hidden md:block" style="width:320px; flex-shrink:0;">
            @foreach($course->modules as $module)
            <div class="mb-3">
                <p style="font-size:0.75rem; font-weight:700; color:#5C5C66; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem; padding:0 0.25rem;">
                    {{ $module->title }}
                </p>
                <div class="flex flex-col gap-1">
                    @foreach($module->lessons as $lesson)
                    @php
                        $isCompleted = in_array($lesson->id, $completedIds);
                        $isUnlocked = in_array($lesson->id, $unlockedIds);
                        $isActive = $openLessonId === $lesson->id;
                        $ytThumb = null;
                        if ($lesson->thumbnail) {
                            $ytThumb = asset('storage/' . $lesson->thumbnail);
                        } elseif ($lesson->video_url && preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([a-zA-Z0-9_-]{11})/', $lesson->video_url, $tm)) {
                            $ytThumb = 'https://img.youtube.com/vi/' . $tm[1] . '/mqdefault.jpg';
                        }
                    @endphp
                    <button
                        @if($isUnlocked && $enrolled) wire:click="toggleLesson({{ $lesson->id }})" @endif
                        style="display:flex; gap:0.5rem; padding:0.5rem; border-radius:0.5rem; text-align:left; width:100%; transition:background 0.15s;
                            {{ $isActive ? 'background:#E8F5E9; border:1px solid #A5D6A7;' : 'background:#FFF; border:1px solid #E1E1E1;' }}
                            {{ !$isUnlocked ? 'opacity:0.5; cursor:default;' : 'cursor:pointer;' }}"
                    >
                        {{-- Thumbnail --}}
                        <div style="width:80px; height:45px; border-radius:0.375rem; overflow:hidden; flex-shrink:0; background:#EEECE9; position:relative;">
                            @if($ytThumb)
                            <img src="{{ $ytThumb }}" alt="" style="width:100%; height:100%; object-fit:cover;">
                            @else
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center;">
                                <span style="font-size:1rem;">▫</span>
                            </div>
                            @endif
                            @if($isCompleted)
                            <div style="position:absolute; top:2px; right:2px; background:#059669; color:#FFF; border-radius:50%; width:16px; height:16px; display:flex; align-items:center; justify-content:center; font-size:0.6rem;">✓</div>
                            @endif
                        </div>
                        {{-- Info --}}
                        <div style="flex:1; min-width:0;">
                            <p style="font-size:0.75rem; font-weight:600; color:{{ $isActive ? '#1B5E20' : ($isUnlocked ? '#1A1A1A' : '#A8A8B0') }}; line-height:1.3; overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">{{ $lesson->title }}</p>
                            @if($lesson->description)
                            <p style="font-size:0.65rem; color:#5C5C66; margin-top:0.125rem; overflow:hidden; display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical;">{{ $lesson->description }}</p>
                            @endif
                            @if($lesson->duration_minutes)
                            <p style="font-size:0.6rem; color:#5C5C66; margin-top:0.125rem;">{{ $lesson->duration_minutes }} phút</p>
                            @endif
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Mobile: lesson list below (shown when no lesson selected) --}}
    @if(!$openLessonId)
    <div class="md:hidden mt-4">
        @foreach($course->modules as $module)
        <div class="mb-3">
            <p style="font-size:0.75rem; font-weight:700; color:#5C5C66; text-transform:uppercase; margin-bottom:0.5rem;">{{ $module->title }}</p>
            @foreach($module->lessons as $lesson)
            @php
                $isCompleted = in_array($lesson->id, $completedIds);
                $isUnlocked = in_array($lesson->id, $unlockedIds);
            @endphp
            <button @if($isUnlocked && $enrolled) wire:click="toggleLesson({{ $lesson->id }})" @endif
                class="card flex items-center gap-3 w-full text-left mb-2" style="padding:0.75rem; {{ !$isUnlocked ? 'opacity:0.5;' : '' }}">
                @if($isCompleted)
                <span style="font-size:1rem;">✓</span>
                @elseif($isUnlocked)
                <span style="background:#d17856; color:#FFF; font-size:0.7rem; font-weight:700; width:1.5rem; height:1.5rem; border-radius:50%; display:flex; align-items:center; justify-content:center;">{{ $lesson->order_index + 1 }}</span>
                @else
                <span>▪</span>
                @endif
                <div style="flex:1;">
                    <p style="font-size:0.825rem; font-weight:600; color:#1A1A1A;">{{ $lesson->title }}</p>
                    @if($lesson->description)
                    <p style="font-size:0.7rem; color:#5C5C66;">{{ $lesson->description }}</p>
                    @endif
                </div>
            </button>
            @endforeach
        </div>
        @endforeach
    </div>
    @endif

    @if($course->modules->isEmpty())
    <div class="card text-center py-8">
        <p style="color:#5C5C66;">Khóa học này chưa có nội dung.</p>
    </div>
    @endif
    @endif
</div>
