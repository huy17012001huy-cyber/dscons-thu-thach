{{-- Quiz section — embedded inside a challenge task. CSS scoped via .quiz-skin wrapper. --}}
<div class="quiz-skin" x-data="{ copied: {} }">
    <style>
        /* Scoped quiz styles — palette warm-paper + accent đỏ cam, không leak ra ngoài */
        .quiz-skin {
            --paper:       #F7FAFC;
            --paper-warm:  #E1F4F7;
            --paper-deep:  #DCECF7;
            --rule:        #D4E1E8;
            --accent:      #1F77BE;
            --accent-soft: #E1F4F7;
            --gold:        #C97700;
            --gold-soft:   #FFF1D6;
            --sage:        #147F96;
            --sage-soft:   #E6F6F8;
            --ink:         #102A3B;
            --ink-soft:    #29485B;
            --ink-mute:    #61798A;
            --shadow:      0 1px 2px rgba(18,59,89,0.05), 0 10px 26px rgba(18,59,89,0.08);
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            margin: 16px 0 8px;
        }
        .quiz-skin .quiz-intro {
            background: var(--gold-soft);
            padding: 22px 26px;
            margin-bottom: 16px;
            border-radius: 16px;
            border: 1px solid rgba(201, 119, 0, 0.3);
        }
        .quiz-skin .quiz-intro h3 {
            margin: 0 0 8px;
            color: var(--gold);
            display: flex; align-items: center; gap: 10px;
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            font-size: 18px; font-weight: 600;
        }
        .quiz-skin .quiz-intro h3::before { content: "🎯"; font-size: 22px; }
        .quiz-skin .quiz-intro p {
            font-size: 13.5px; color: var(--ink-soft); line-height: 1.55; margin: 6px 0;
        }
        .quiz-skin .quiz-progress {
            background: var(--paper);
            padding: 12px 16px;
            border-radius: 8px;
            margin: 0 0 18px;
            text-align: center;
            border: 1px solid var(--rule);
        }
        .quiz-skin .quiz-progress-text {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            font-size: 12px; color: var(--ink-soft); letter-spacing: 0.05em;
        }
        .quiz-skin .quiz-progress-bar {
            height: 4px; background: var(--paper-deep);
            border-radius: 2px; margin-top: 8px; overflow: hidden;
        }
        .quiz-skin .quiz-progress-fill {
            height: 100%; background: var(--sage);
            transition: width 0.4s ease; width: 0%;
        }
        .quiz-skin .quiz-card {
            background: white;
            padding: 22px 24px;
            margin: 14px 0;
            border-radius: 16px;
            border: 1px solid var(--rule);
            box-shadow: var(--shadow);
        }
        .quiz-skin .quiz-card.answered {
            border-color: var(--sage);
            box-shadow: 0 0 0 1px var(--sage), var(--shadow);
        }
        .quiz-skin .quiz-num {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            font-size: 11px; letter-spacing: 0.15em;
            color: var(--accent); text-transform: uppercase;
            font-weight: 600; margin-bottom: 10px;
        }
        .quiz-skin .quiz-question {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            font-size: 17px; font-weight: 500;
            color: var(--ink); line-height: 1.45;
            margin-bottom: 16px;
        }
        .quiz-skin .quiz-question strong { font-weight: 700; color: var(--ink); }
        .quiz-skin .quiz-helper {
            background: var(--paper-warm);
            padding: 10px 14px;
            margin: 0 0 14px;
            border-radius: 8px;
            font-size: 12.5px; color: var(--ink-soft);
            display: flex; align-items: center; justify-content: space-between;
            gap: 10px; flex-wrap: wrap;
        }
        .quiz-skin .quiz-helper-text { flex: 1; min-width: 200px; }
        .quiz-skin .quiz-ask-btn {
            background: var(--ink);
            color: #FFFFFF;
            border: none;
            padding: 7px 12px;
            border-radius: 6px;
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            font-size: 10.5px; letter-spacing: 0.05em;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .quiz-skin .quiz-ask-btn:hover { background: var(--accent); }
        .quiz-skin .quiz-ask-btn.copied { background: var(--sage); }
        .quiz-skin .quiz-options {
            display: flex; flex-direction: column; gap: 8px;
        }
        .quiz-skin .quiz-option {
            background: var(--paper);
            padding: 12px 16px;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid transparent;
            font-size: 14px; color: var(--ink); line-height: 1.5;
            transition: background 0.15s, border-color 0.15s;
            user-select: none;
        }
        .quiz-skin .quiz-option:hover { background: var(--paper-deep); }
        .quiz-skin .quiz-option.selected {
            border-color: var(--accent); background: var(--accent-soft);
        }
        .quiz-skin .quiz-option.correct {
            border-color: var(--sage); background: var(--sage-soft);
        }
        .quiz-skin .quiz-option.wrong {
            border-color: #d96b4e; background: #fde8e0; opacity: 0.78;
        }
        .quiz-skin .quiz-option.locked { cursor: default; }
        .quiz-skin .quiz-option .option-letter {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            font-weight: 600; color: var(--accent);
            margin-right: 10px;
        }
        .quiz-skin .quiz-option.correct .option-letter { color: var(--sage); }
        .quiz-skin .quiz-option.wrong .option-letter { color: #d96b4e; }
        .quiz-skin .quiz-actions {
            margin-top: 12px;
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        }
        .quiz-skin .quiz-submit {
            background: var(--accent);
            color: white;
            border: none;
            padding: 9px 20px;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 13.5px; font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .quiz-skin .quiz-submit:hover:not(:disabled) { background: #b03a18; }
        .quiz-skin .quiz-submit:disabled {
            background: var(--ink-mute); cursor: not-allowed; opacity: 0.5;
        }
        .quiz-skin .quiz-retry {
            background: transparent;
            border: 1px solid var(--rule);
            color: var(--ink-soft);
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12.5px; cursor: pointer;
            transition: border-color .15s ease, color .15s ease, background-color .15s ease, box-shadow .15s ease;
        }
        .quiz-skin .quiz-retry:hover { border-color: var(--accent); color: var(--accent); }
        .quiz-skin .quiz-feedback {
            margin-top: 14px;
            padding: 14px 18px;
            border-radius: 8px;
            font-size: 13.5px; line-height: 1.55;
        }
        .quiz-skin .quiz-feedback.correct {
            background: var(--sage-soft); border-left: 3px solid var(--sage); color: var(--ink);
        }
        .quiz-skin .quiz-feedback.wrong {
            background: #fde8e0; border-left: 3px solid #d96b4e; color: var(--ink);
        }
        .quiz-skin .quiz-feedback strong {
            display: block; margin-bottom: 6px;
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; font-size: 15px;
        }
        .quiz-skin .quiz-feedback.correct strong { color: var(--sage); }
        .quiz-skin .quiz-feedback.wrong strong { color: #b54a2c; }
        .quiz-skin .quiz-feedback p { margin: 4px 0; }
        .quiz-skin .quiz-feedback em { font-style: italic; color: var(--ink-soft); }
        .quiz-skin .quiz-success {
            background: var(--sage-soft);
            padding: 22px 26px;
            margin-top: 18px;
            border-radius: 16px;
            border: 1px solid var(--sage);
            text-align: center;
        }
        .quiz-skin .quiz-success-title {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            font-size: 18px; font-weight: 600;
            color: var(--sage); margin-bottom: 8px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .quiz-skin .quiz-success-title::before { content: "🏆"; font-size: 22px; }
        .quiz-skin .quiz-success p { color: var(--ink-soft); font-size: 14px; margin: 4px 0; }
        @media (max-width: 720px) {
            .quiz-skin .quiz-card { padding: 18px 18px; }
            .quiz-skin .quiz-question { font-size: 16px; }
            .quiz-skin .quiz-helper { flex-direction: column; align-items: stretch; }
            .quiz-skin .quiz-ask-btn { width: 100%; }
        }
    </style>

    @php
        $questions = $task->quiz_json ?? [];
        $quizConfig = config('communities.quiz.' . brand()->slug, config('communities.quiz.default'));
    @endphp

    <div class="quiz-intro">
        <h3>Trắc nghiệm kỹ thuật</h3>
        <p>Mỗi câu là 1 tình huống thật. Bạn phải đoán: <strong>cấp skill nào phù hợp nhất</strong>?</p>
        @if($quizConfig['show_agent_helper'])
            <p><strong>Quy tắc:</strong> dùng công cụ hỗ trợ phù hợp để phân tích tình huống, sau đó quay lại đây chọn đáp án.</p>
        @else
            <p><strong>Quy tắc:</strong> đọc kỹ tình huống và đối chiếu với quy trình kỹ thuật trước khi chọn đáp án.</p>
        @endif
        <p>⚠ <strong>Chốt 1 lần là chốt vĩnh viễn</strong> — không retry, không sửa. Mỗi câu đúng được +2 XP.</p>
    </div>

    <div class="quiz-progress">
        <div class="quiz-progress-text">{{ $this->progress }} / {{ $this->total }} câu đã trả lời · Score {{ $this->score }}/{{ $this->total }}</div>
        <div class="quiz-progress-bar">
            <div class="quiz-progress-fill" style="width: {{ $this->total > 0 ? round($this->progress / $this->total * 100) : 0 }}%;"></div>
        </div>
    </div>

    @foreach($questions as $idx => $q)
        @php
            $sub          = $this->submitted[$idx] ?? null;
            $isAnswered   = $sub !== null;
            $isCorrect    = $sub['is_correct'] ?? false;
            $userLetter   = $sub['letter'] ?? null;
            $correctLetter= $q['correct'] ?? null;
        @endphp
        <div class="quiz-card {{ $isAnswered ? 'answered' : '' }}" wire:key="quiz-q-{{ $idx }}">
            <div class="quiz-num">Câu {{ str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT) }} / {{ count($questions) }}</div>
            <div class="quiz-question">{!! Str::markdown($q['q'] ?? '', ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>

            @if($quizConfig['show_agent_helper'])
                <div class="quiz-helper">
                    <span class="quiz-helper-text">⚠ <strong>Chỉ chọn 1 lần</strong> — hãy phân tích kỹ trước khi chọn đáp án.</span>
                    <button type="button"
                        class="quiz-ask-btn"
                        :class="copied[{{ $idx }}] ? 'copied' : ''"
                        @click.prevent="navigator.clipboard.writeText(@js($q['ai_prompt'] ?? '')).then(() => { copied[{{ $idx }}] = true; setTimeout(() => copied[{{ $idx }}] = false, 2200); }).catch(() => { copied[{{ $idx }}] = true; setTimeout(() => copied[{{ $idx }}] = false, 2200); });">
                        <span x-show="!copied[{{ $idx }}]">Xem gợi ý</span>
                        <span x-show="copied[{{ $idx }}]" x-cloak>Đã sao chép</span>
                    </button>
                </div>
            @else
                <div class="quiz-helper">
                    <span class="quiz-helper-text">⚠ <strong>Chỉ chọn 1 lần</strong> — đối chiếu với quy trình kỹ thuật trước khi quyết định.</span>
                </div>
            @endif

            <div class="quiz-options">
                @foreach($q['options'] ?? [] as $letter => $text)
                    @php
                        $classes = ['quiz-option'];
                        if ($isAnswered) {
                            $classes[] = 'locked';
                            if ($letter === $correctLetter) $classes[] = 'correct';
                            elseif ($letter === $userLetter && !$isCorrect) $classes[] = 'wrong';
                        }
                    @endphp
                    <div class="{{ implode(' ', $classes) }}"
                        @if(!$isAnswered)
                            wire:click="answerQuestion({{ $idx }}, '{{ $letter }}')"
                            wire:loading.class="loading-disabled"
                            wire:target="answerQuestion"
                            wire:confirm="Chọn đáp án {{ $letter }}? Sau khi chọn không sửa lại được."
                        @endif>
                        <span class="option-letter">{{ $letter }}.</span>{{ $text }}
                    </div>
                @endforeach
            </div>

            @if($isAnswered)
                <div class="quiz-feedback {{ $isCorrect ? 'correct' : 'wrong' }}">
                    <strong>{{ $isCorrect ? '✓ Đúng rồi!' : '✗ Chưa đúng' }}</strong>
                    <div>{!! Str::markdown($q['explanation'] ?? '', ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
                </div>
            @endif
        </div>
    @endforeach

    @if($this->progress === $this->total && $this->total > 0)
        <div class="quiz-success">
            <div class="quiz-success-title">Hoàn thành {{ $this->total }} câu!</div>
            <p>Score của bạn: <strong>{{ $this->score }}/{{ $this->total }}</strong></p>
            <p>Giờ là lúc tự tay tạo skill đầu tiên — cuộn xuống dưới để nộp bài.</p>
        </div>
    @endif
</div>
