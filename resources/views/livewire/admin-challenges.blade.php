<div>
    <div class="flex items-center justify-between mb-4">
        <h1 style="font-size:1.25rem; font-weight:800; color:#1A1A1A;">■ Quản lý Challenge</h1>
        <button wire:click="openCreateExpedition" class="btn btn-primary" style="font-size:0.8rem;">+ Tạo Challenge</button>
    </div>

    {{-- Expedition list --}}
    <div style="display:flex; flex-direction:column; gap:0.75rem;">
        @forelse($expeditions as $exp)
        <div class="card" style="padding:1rem;">
            <div class="flex items-start justify-between gap-2">
                <div style="flex:1; min-width:0;">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span style="font-weight:700; color:#1A1A1A; font-size:0.9rem;">{{ $exp->title }}</span>
                        <span class="badge" style="font-size:0.65rem; background:{{ match($exp->status) { 'active'=>'#DCFCE7','open'=>'#DBEAFE','completed'=>'#F3F4F6','failed'=>'#FEE2E2',default=>'#F3F4F6' } }}; color:{{ match($exp->status) { 'active'=>'#166534','open'=>'#1E40AF','completed'=>'#374151','failed'=>'#991B1B',default=>'#374151' } }};">{{ $exp->status }}</span>
                        <span class="badge" style="font-size:0.65rem; background:#FEF9C3; color:#713F12;">{{ $exp->difficulty }}</span>
                    </div>
                    <div style="font-size:0.75rem; color:#5C5C66; margin-top:0.25rem;">
                        {{ $exp->required_days }} ngày · tối đa {{ $exp->max_members }} người · {{ $exp->members_count }} đã tham gia · {{ $exp->tasks_count }} tasks
                        @if($exp->slug) · <span style="font-family:monospace;">{{ $exp->slug }}</span>@endif
                    </div>
                </div>
                <div class="flex gap-1 shrink-0">
                    <button wire:click="toggleManageTasks({{ $exp->id }})" class="btn btn-ghost" style="font-size:0.7rem; padding:0.2rem 0.5rem; {{ $managingExpeditionId === $exp->id ? 'background:#DCFCE7; color:#166534;' : '' }}">
                        📋 Tasks
                    </button>
                    <button wire:click="openFreezeModal({{ $exp->id }})" class="btn btn-ghost" style="font-size:0.7rem; padding:0.2rem 0.5rem; {{ $exp->freeze_from_day ? 'background:#FEF3C7; color:#92400E;' : '' }}">⏸ Tạm dừng</button>
                    <button wire:click="openEditExpedition({{ $exp->id }})" class="btn btn-ghost" style="font-size:0.7rem; padding:0.2rem 0.5rem;">✏️ Sửa</button>
                    <button wire:click="deleteExpedition({{ $exp->id }})" wire:confirm="Xóa challenge này? Toàn bộ tasks và dữ liệu sẽ bị xóa." class="btn btn-ghost" style="font-size:0.7rem; padding:0.2rem 0.5rem; color:#DC2626;">🗑 Xóa</button>
                </div>
            </div>

            {{-- Freeze status strip --}}
            @if($exp->freeze_from_day && $exp->freeze_ends_at && now()->lessThan($exp->freeze_ends_at))
            <div style="margin-top:0.5rem; background:#FEF3C7; border:1px solid #FDE68A; border-radius:0.375rem; padding:0.4rem 0.6rem; display:flex; align-items:center; justify-content:space-between; gap:0.5rem; font-size:0.72rem;">
                <span style="color:#92400E; font-weight:600;">⏸ Đang đóng băng từ ngày {{ $exp->freeze_from_day }} — tiếp tục {{ $exp->freeze_ends_at->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') }}</span>
                <button wire:click="clearFreeze({{ $exp->id }})" wire:confirm="Bỏ đóng băng challenge này?" class="btn btn-ghost" style="font-size:0.65rem; padding:0.15rem 0.4rem; color:#DC2626;">Bỏ đóng băng</button>
            </div>
            @endif

            {{-- Task management panel --}}
            @if($managingExpeditionId === $exp->id)
            <div style="margin-top:1rem; border-top:1px solid #E1E1E1; padding-top:1rem;">
                <div class="flex items-center justify-between mb-3">
                    <span style="font-size:0.8rem; font-weight:700; color:#1A1A1A;">Tasks ({{ $managingTasks->count() }})</span>
                    <button wire:click="openCreateTask" class="btn btn-primary" style="font-size:0.7rem; padding:0.2rem 0.6rem;">+ Thêm task</button>
                </div>

                @if($managingTasks->isEmpty())
                <p style="font-size:0.8rem; color:#5C5C66; text-align:center; padding:1rem 0;">Chưa có task nào. Nhấn "+ Thêm task" để tạo.</p>
                @else
                <div style="display:flex; flex-direction:column; gap:0.5rem;">
                    @foreach($managingTasks as $task)
                    <div style="display:flex; align-items:center; gap:0.75rem; padding:0.5rem 0.75rem; background:#F7F5F3; border-radius:0.375rem;">
                        <span style="font-size:0.7rem; font-weight:700; color:#d17856; min-width:48px;">{{ $task->label ?: 'Ngày ' . $task->day_number }}</span>
                        <span style="flex:1; font-size:0.8rem; color:#1A1A1A; font-weight:500;">{{ $task->title }}</span>
                        <span style="font-size:0.65rem; color:#5C5C66; background:#E5E7EB; padding:0.1rem 0.35rem; border-radius:0.25rem;">{{ $task->evidence_type }}</span>
                        @if($task->video_url)
                        <span style="font-size:0.65rem; color:#2563EB;">▶ video</span>
                        @endif
                        @if($task->meeting_at)
                        <span style="font-size:0.65rem; color:#7C3AED;">📅 meeting</span>
                        @endif
                        @if(!empty($task->quiz_json))
                        <span style="font-size:0.65rem; color:#C2410C;">📝 quiz · {{ count($task->quiz_json) }} câu</span>
                        @endif
                        <div class="flex gap-1">
                            @if(!empty($task->quiz_json))
                            <button wire:click="toggleQuizReport({{ $task->id }})" class="btn btn-ghost" style="font-size:0.65rem; padding:0.15rem 0.4rem; {{ $quizReportTaskId === $task->id ? 'background:#FCE4DC; color:#C2410C;' : '' }}" title="Báo cáo quiz">📊</button>
                            @endif
                            <button wire:click="openEditTask({{ $task->id }})" class="btn btn-ghost" style="font-size:0.65rem; padding:0.15rem 0.4rem;">✏️</button>
                            <button wire:click="deleteTask({{ $task->id }})" wire:confirm="Xóa task ngày {{ $task->day_number }}?" class="btn btn-ghost" style="font-size:0.65rem; padding:0.15rem 0.4rem; color:#DC2626;">🗑</button>
                        </div>
                    </div>

                    {{-- Quiz report panel (inline below task row) --}}
                    @if($quizReportTaskId === $task->id && !empty($task->quiz_json))
                    @php $report = $this->getQuizReportData($task); @endphp
                    <div style="background:#FFF; border:1px solid #FDE68A; border-radius:0.5rem; padding:1rem; margin-top:-0.25rem;">
                        <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:0.75rem; flex-wrap:wrap; gap:0.5rem;">
                            <h3 style="font-size:0.85rem; font-weight:700; color:#1A1A1A;">📊 Quiz Report — Ngày {{ $task->day_number }}</h3>
                            <span style="font-size:0.7rem; color:#5C5C66;">{{ $report['total_users'] }} người đã làm · {{ $report['total_questions'] }} câu</span>
                        </div>

                        @if($report['total_users'] === 0)
                        <p style="font-size:0.78rem; color:#5C5C66; text-align:center; padding:0.75rem 0;">Chưa có ai làm quiz này.</p>
                        @else

                        {{-- Per-question stats --}}
                        <div style="margin-bottom:0.875rem;">
                            <p style="font-size:0.72rem; font-weight:700; color:#5C5C66; margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.04em;">Tỉ lệ đúng theo câu</p>
                            <div style="display:flex; flex-direction:column; gap:0.25rem;">
                                @foreach($report['per_question'] as $idx => $stat)
                                @php
                                    $pct = $stat['total'] > 0 ? round($stat['rate'] * 100) : 0;
                                    $isLow = $stat['total'] > 0 && $stat['rate'] < 0.5;
                                    $barColor = $isLow ? '#DC2626' : ($stat['rate'] < 0.7 ? '#F59E0B' : '#16A34A');
                                @endphp
                                <div style="display:flex; align-items:center; gap:0.5rem; font-size:0.72rem;">
                                    <span style="min-width:48px; color:#5C5C66; font-family:monospace;">Câu {{ str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                    <div style="flex:1; height:6px; background:#F3F4F6; border-radius:3px; overflow:hidden;">
                                        <div style="width:{{ $pct }}%; height:100%; background:{{ $barColor }}; transition:width 0.3s;"></div>
                                    </div>
                                    <span style="min-width:80px; text-align:right; color:{{ $isLow ? '#DC2626' : '#1A1A1A' }}; font-weight:{{ $isLow ? 700 : 500 }};">{{ $stat['correct'] }}/{{ $stat['total'] }} · {{ $pct }}%</span>
                                    @if($isLow)
                                    <span style="font-size:0.65rem; color:#DC2626; font-weight:600;">⚠ cần dạy lại</span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Per-user scoreboard --}}
                        <div>
                            <p style="font-size:0.72rem; font-weight:700; color:#5C5C66; margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.04em;">Bảng điểm</p>
                            <div style="display:grid; grid-template-columns:1fr 90px 60px; gap:0.5rem 0.75rem; font-size:0.75rem;">
                                <span style="font-weight:600; color:#5C5C66; font-size:0.68rem;">Tên</span>
                                <span style="font-weight:600; color:#5C5C66; font-size:0.68rem;">Tiến độ</span>
                                <span style="font-weight:600; color:#5C5C66; font-size:0.68rem; text-align:right;">Score</span>
                                @foreach($report['users'] as $u)
                                <a href="{{ route('profile', $u['username'] ?: $u['id']) }}" style="color:#1A73E8; text-decoration:none;">{{ $u['name'] }}</a>
                                <span style="color:#5C5C66;">{{ $u['answered'] }}/{{ $report['total_questions'] }} ({{ round($u['completion'] * 100) }}%)</span>
                                <span style="text-align:right; font-weight:600; color:{{ $u['score'] === $report['total_questions'] ? '#16A34A' : ($u['score'] >= ceil($report['total_questions'] * 0.7) ? '#1A1A1A' : '#92400E') }};">{{ $u['score'] }}/{{ $report['total_questions'] }}</span>
                                @endforeach
                            </div>
                        </div>

                        @endif
                    </div>
                    @endif
                    @endforeach
                </div>
                @endif
            </div>
            @endif
        </div>
        @empty
        <div class="card" style="padding:2rem; text-align:center; color:#5C5C66;">Chưa có challenge nào.</div>
        @endforelse
    </div>

    {{-- Expedition Modal --}}
    @if($showExpeditionModal)
    <div style="position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:100; display:flex; align-items:center; justify-content:center; padding:1rem;" wire:click.self="$set('showExpeditionModal', false)">
        <div class="card" style="width:100%; max-width:560px; max-height:90vh; overflow-y:auto; padding:1.5rem;">
            <h2 style="font-size:1rem; font-weight:700; color:#1A1A1A; margin-bottom:1.25rem;">
                {{ $editingExpeditionId ? 'Sửa Challenge' : 'Tạo Challenge mới' }}
            </h2>

            <div style="display:flex; flex-direction:column; gap:0.75rem;">
                {{-- Title --}}
                <div>
                    <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Tên challenge <span style="color:#DC2626;">*</span></label>
                    <input wire:model="expTitle" type="text" class="input" style="width:100%;" placeholder="VD: AI Agent Challenge 21 Day">
                    @error('expTitle') <p style="color:#DC2626; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                </div>

                {{-- Slug --}}
                <div>
                    <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Slug (URL) <span style="font-weight:400; color:#9CA3AF;">— để trống tự generate</span></label>
                    <input wire:model="expSlug" type="text" class="input" style="width:100%; font-family:monospace;" placeholder="ai-agent-challenge-21-day">
                </div>

                {{-- Boss name --}}
                <div>
                    <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Tên Boss / Mục tiêu <span style="color:#DC2626;">*</span></label>
                    <input wire:model="expBossName" type="text" class="input" style="width:100%;" placeholder="VD: Đạt 100 khách hàng trong 30 ngày">
                    @error('expBossName') <p style="color:#DC2626; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                </div>

                {{-- Leader --}}
                <div>
                    <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Leader <span style="color:#DC2626;">*</span> <span style="font-weight:400; color:#9CA3AF;">— chỉ Admin/Mod</span></label>
                    <select wire:model.number="expLeaderId" class="input" style="width:100%;">
                        <option value="">— Chưa chỉ định —</option>
                        @foreach($staffUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ '@'.$u->username }})</option>
                        @endforeach
                    </select>
                    @error('expLeaderId') <p style="color:#DC2626; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Mô tả</label>
                    <textarea wire:model="expDescription" class="input" rows="3" style="width:100%; resize:vertical;" placeholder="Mô tả ngắn về challenge..."></textarea>
                </div>

                {{-- Difficulty + Status --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                    <div>
                        <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Độ khó <span style="color:#DC2626;">*</span></label>
                        <select wire:model="expDifficulty" class="input" style="width:100%;">
                            <option value="normal">Normal</option>
                            <option value="hard">Hard</option>
                            <option value="chaos">Chaos</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Status <span style="color:#DC2626;">*</span></label>
                        <select wire:model="expStatus" class="input" style="width:100%;">
                            <option value="open">Open</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>

                {{-- Days + Members --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                    <div>
                        <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Số ngày <span style="color:#DC2626;">*</span></label>
                        <input wire:model="expRequiredDays" type="number" min="1" class="input" style="width:100%;">
                        @error('expRequiredDays') <p style="color:#DC2626; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Số member tối đa <span style="color:#DC2626;">*</span></label>
                        <input wire:model="expMaxMembers" type="number" min="1" class="input" style="width:100%;">
                    </div>
                </div>

                {{-- Deposit + Price --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                    <div>
                        <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Deposit AIP</label>
                        <input wire:model="expDepositAip" type="number" min="0" class="input" style="width:100%;">
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Giá (VNĐ)</label>
                        <input wire:model="expPrice" type="number" min="0" class="input" style="width:100%;" placeholder="Để trống nếu miễn phí">
                    </div>
                </div>

                {{-- Starts at + Ends at --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                    <div>
                        <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Ngày bắt đầu</label>
                        <input wire:model="expStartsAt" type="datetime-local" class="input" style="width:100%;">
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Ngày kết thúc</label>
                        <input wire:model="expEndsAt" type="datetime-local" class="input" style="width:100%;">
                    </div>
                </div>
            </div>

            <div class="flex gap-2 mt-5">
                <button wire:click="saveExpedition"
                        wire:loading.attr="disabled" wire:target="saveExpedition"
                        class="btn btn-primary" style="flex:1;">
                    <span wire:loading.remove wire:target="saveExpedition">{{ $editingExpeditionId ? 'Lưu thay đổi' : 'Tạo Challenge' }}</span>
                    <span wire:loading wire:target="saveExpedition">Đang lưu...</span>
                </button>
                <button wire:click="$set('showExpeditionModal', false)" class="btn btn-ghost" style="flex:1;">Hủy</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Task Modal --}}
    @if($showTaskModal)
    <div style="position:fixed; inset:0; z-index:100; display:flex; align-items:center; justify-content:center; padding:1rem; pointer-events:none;">
        <div class="card" style="width:100%; max-width:900px; max-height:90vh; overflow-y:auto; padding:1.5rem; pointer-events:auto; box-shadow:0 20px 50px rgba(0,0,0,0.25);">
            <h2 style="font-size:1rem; font-weight:700; color:#1A1A1A; margin-bottom:1.25rem;">
                {{ $editingTaskId ? 'Sửa Task' : 'Thêm Task mới' }}
            </h2>

            <div style="display:flex; flex-direction:column; gap:0.75rem;">
                {{-- Day + Label + Title --}}
                <div style="display:grid; grid-template-columns:90px 130px 1fr; gap:0.75rem; align-items:start;">
                    <div>
                        <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Số thứ tự <span style="color:#DC2626;">*</span></label>
                        <input wire:model="taskDayNumber" type="number" min="1" class="input" style="width:100%;">
                        @error('taskDayNumber') <p style="color:#DC2626; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Nhãn hiển thị</label>
                        <input wire:model="taskLabel" type="text" class="input" style="width:100%;" placeholder="VD: Ngày 1, Buổi 1...">
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Tên nhiệm vụ <span style="color:#DC2626;">*</span></label>
                        <input wire:model="taskTitle" type="text" class="input" style="width:100%;" placeholder="VD: Kick Off — Tham gia Meeting">
                        @error('taskTitle') <p style="color:#DC2626; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Mô tả ngắn</label>
                    <textarea wire:model="taskDescription" class="input" rows="3" style="width:100%; resize:vertical;" placeholder="Mô tả nhiệm vụ..." x-data @paste="window.pasteAsMarkdown($event)"></textarea>
                </div>

                {{-- SOP --}}
                <div>
                    <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">SOP / Hướng dẫn chi tiết</label>
                    <textarea wire:model="taskSopContent" class="input" rows="4" style="width:100%; resize:vertical;" placeholder="Viết hướng dẫn chi tiết..." x-data @paste="window.pasteAsMarkdown($event)"></textarea>
                </div>

                {{-- Video URL --}}
                <div>
                    <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Video / Meeting URLs <span style="font-weight:400; color:#9CA3AF;">— mỗi dòng 1 link</span></label>
                    <textarea wire:model="taskVideoUrl" class="input" rows="3" style="width:100%; resize:vertical; font-family:monospace; font-size:0.75rem;" placeholder="https://youtube.com/watch?v=...&#10;https://meet.google.com/..."></textarea>
                </div>

                {{-- Meeting at --}}
                <div>
                    <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Lịch Meeting (giờ VN)</label>
                    <input wire:model="taskMeetingAt" type="datetime-local" class="input" style="width:100%;">
                </div>

                {{-- Evidence type + label --}}
                <div style="display:grid; grid-template-columns:140px 1fr; gap:0.75rem; align-items:start;">
                    <div>
                        <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Loại bằng chứng <span style="color:#DC2626;">*</span></label>
                        <select wire:model="taskEvidenceType" class="input" style="width:100%;">
                            <option value="text">Text</option>
                            <option value="screenshot">Screenshot</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Yêu cầu bằng chứng</label>
                        <textarea wire:model="taskEvidenceLabel" class="input" rows="2" style="width:100%; resize:vertical;" placeholder="VD: Chụp màn hình kết quả"></textarea>
                    </div>
                </div>

                {{-- Admin note --}}
                <div>
                    <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Nhắn nhủ cho thành viên <span style="font-weight:400; color:#9CA3AF;">— hiện sau khi hoàn thành</span></label>
                    <textarea wire:model="taskAdminNote" class="input" rows="2" style="width:100%; resize:vertical;" placeholder="Lời nhắn từ admin..."></textarea>
                </div>

                {{-- Contest mode + Duration --}}
                <div style="padding:0.75rem; background:#FEF3C7; border:1px solid #FDE68A; border-radius:8px;">
                    <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.85rem; font-weight:600; color:#92400E; cursor:pointer;">
                        <input wire:model.live="taskIsContest" type="checkbox" style="width:16px; height:16px;">
                        Chế độ thi đấu (cho phép nộp bài nhiều lần)
                    </label>
                    <p style="font-size:0.7rem; color:#92400E; margin-top:0.25rem; line-height:1.4;">Bật để member nộp được nhiều bài cho cùng task. XP chỉ cộng 1 lần. Reject/duyệt từng bài độc lập.</p>

                    @if($taskIsContest)
                    <div style="margin-top:0.75rem; display:flex; flex-direction:column; gap:0.5rem;">
                        <div>
                            <label style="font-size:0.8rem; font-weight:600; color:#92400E; display:block; margin-bottom:0.25rem;">Tiêu đề mini-game</label>
                            <input wire:model="taskContestTitle" type="text" class="input" style="width:100%;" placeholder="VD: 🏆 Mini game — nộp càng nhiều bài được duyệt càng dễ thắng">
                            <p style="font-size:0.65rem; color:#92400E; margin-top:0.2rem;">Để trống = dùng tiêu đề mặc định trong code.</p>
                        </div>
                        <div>
                            <label style="font-size:0.8rem; font-weight:600; color:#92400E; display:block; margin-bottom:0.25rem;">Mô tả / luật chơi</label>
                            <textarea wire:model="taskContestDescription" class="input" rows="3"
                                x-data x-init="$el.style.height = $el.scrollHeight + 'px'"
                                @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                style="overflow:hidden; resize:none; font-size:0.78rem; width:100%;"
                                placeholder="Mô tả luật chơi, giải thưởng, hạn nộp..."></textarea>
                            <p style="font-size:0.65rem; color:#92400E; margin-top:0.2rem;">Để trống = dùng mô tả mặc định trong code.</p>
                        </div>
                    </div>
                    @endif

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-top:0.75rem;">
                        <div>
                            <label style="font-size:0.8rem; font-weight:600; color:#92400E; display:block; margin-bottom:0.25rem;">Ngày kế mở sau (giờ)</label>
                            <input wire:model="taskDurationHours" type="number" min="1" step="1" class="input" style="width:100%;" placeholder="24">
                            @error('taskDurationHours') <p style="color:#DC2626; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                            <p style="font-size:0.65rem; color:#92400E; margin-top:0.2rem;">Mặc định 24. Sau số giờ này thì ngày kế tiếp tự mở cho member.</p>
                        </div>
                        @if($taskIsContest)
                        <div>
                            <label style="font-size:0.8rem; font-weight:600; color:#92400E; display:block; margin-bottom:0.25rem;">Thời lượng minigame (giờ)</label>
                            <input wire:model="taskContestDurationHours" type="number" min="1" step="1" class="input" style="width:100%;" placeholder="Để trống = dùng 'Ngày kế mở sau'">
                            <p style="font-size:0.65rem; color:#92400E; margin-top:0.2rem;">Minigame chạy trong bao lâu thì đóng. Để trống = đóng cùng lúc ngày kế mở.</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Reward file (tải về sau khi hoàn thành) --}}
                <div style="padding:0.75rem; background:#EFF6FF; border:1px solid #BFDBFE; border-radius:8px;">
                    <label style="font-size:0.85rem; font-weight:600; color:#1E40AF; display:block; margin-bottom:0.25rem;">
                        📥 Tài liệu thưởng <span style="font-weight:400; color:#6B7280;">— hiện cho user sau khi bài được duyệt</span>
                    </label>
                    @if($existingRewardFile)
                    <div style="display:flex; align-items:center; gap:0.5rem; padding:0.5rem; background:#DBEAFE; border-radius:6px; margin-bottom:0.5rem;">
                        <span style="font-size:0.75rem; color:#1E40AF; flex:1;">📄 {{ basename($existingRewardFile) }}</span>
                        <button wire:click="removeRewardFile" type="button" wire:confirm="Xoá file thưởng này?" style="font-size:0.7rem; color:#DC2626; background:transparent; border:none; cursor:pointer;">Xoá</button>
                    </div>
                    @endif
                    <input wire:model="taskRewardFile" type="file" accept=".pdf,.zip,.doc,.docx,.xlsx,.xls,.png,.jpg,.jpeg" style="font-size:0.8rem; width:100%;">
                    @error('taskRewardFile') <p style="color:#DC2626; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                    <p style="font-size:0.65rem; color:#6B7280; margin-top:0.25rem;">PDF, ZIP, DOC, XLSX, ảnh. Max 20MB. Upload file mới sẽ thay file cũ.</p>

                    <label style="font-size:0.8rem; font-weight:600; color:#1E40AF; display:block; margin-top:0.6rem; margin-bottom:0.25rem;">Tên hiển thị (tuỳ chọn)</label>
                    <input wire:model="taskRewardFileLabel" class="input" style="width:100%; font-size:0.8rem;" placeholder="VD: Slide hướng dẫn Day 16.pdf">
                    <p style="font-size:0.65rem; color:#6B7280; margin-top:0.2rem;">Để trống → dùng tên file gốc khi user tải về.</p>
                </div>

                {{-- Quiz JSON editor --}}
                <div>
                    <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">
                        📝 Quiz (JSON) <span style="font-weight:400; color:#9CA3AF;">— để trống nếu task không có quiz</span>
                    </label>
                    <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:0.375rem; padding:0.5rem 0.625rem; margin-bottom:0.375rem; font-size:0.7rem; color:#78716C; line-height:1.5;">
                        Mỗi phần tử cần: <code style="background:#FEF3C7; padding:0.05rem 0.25rem; border-radius:3px;">q</code> (câu hỏi), <code style="background:#FEF3C7; padding:0.05rem 0.25rem; border-radius:3px;">options{A,B,C,D}</code>, <code style="background:#FEF3C7; padding:0.05rem 0.25rem; border-radius:3px;">correct</code> (A/B/C/D), <code style="background:#FEF3C7; padding:0.05rem 0.25rem; border-radius:3px;">explanation</code>, <code style="background:#FEF3C7; padding:0.05rem 0.25rem; border-radius:3px;">ai_prompt</code>. Markdown <code style="background:#FEF3C7; padding:0.05rem 0.25rem; border-radius:3px;">**bold**</code> hoạt động trong q + explanation. Mỗi câu đúng = +2 XP cho user.
                    </div>
                    <textarea wire:model="taskQuizJson" rows="10" class="input" style="width:100%; font-family:monospace; font-size:0.72rem; line-height:1.5; resize:vertical; min-height:140px;" placeholder='[
  {
    "q": "Câu hỏi 1...",
    "options": {"A": "...", "B": "...", "C": "...", "D": "..."},
    "correct": "A",
    "explanation": "Giải thích...",
    "ai_prompt": "Prompt copy cho user..."
  }
]'></textarea>
                    @error('taskQuizJson') <p style="color:#DC2626; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex gap-2 mt-5">
                <button wire:click="saveTask"
                        wire:loading.attr="disabled" wire:target="saveTask"
                        class="btn btn-primary" style="flex:1;">
                    <span wire:loading.remove wire:target="saveTask">{{ $editingTaskId ? 'Lưu thay đổi' : 'Thêm Task' }}</span>
                    <span wire:loading wire:target="saveTask">Đang lưu...</span>
                </button>
                <button wire:click="$set('showTaskModal', false)" class="btn btn-ghost" style="flex:1;">Hủy</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Freeze Modal --}}
    @if($showFreezeModal)
    <div style="position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:100; display:flex; align-items:center; justify-content:center; padding:1rem;" wire:click.self="$set('showFreezeModal', false)">
        <div class="card" style="width:100%; max-width:460px; padding:1.5rem;">
            <h2 style="font-size:1rem; font-weight:700; color:#1A1A1A; margin-bottom:0.5rem;">⏸ Tạm dừng Challenge</h2>
            <p style="font-size:0.78rem; color:#5C5C66; margin-bottom:1rem; line-height:1.5;">
                Member đang ở ngày này trở đi sẽ được đóng băng timer — không tính trễ. Member chưa tới sẽ chạy bình thường cho đến khi gặp ngày đóng băng.
            </p>

            <div style="display:flex; flex-direction:column; gap:0.75rem;">
                <div>
                    <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Đóng băng từ ngày <span style="color:#DC2626;">*</span></label>
                    <input wire:model="freezeFromDay" type="number" min="1" class="input" style="width:100%;" placeholder="VD: 13">
                    @error('freezeFromDay') <p style="color:#DC2626; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label style="font-size:0.8rem; font-weight:600; color:#5C5C66; display:block; margin-bottom:0.25rem;">Tiếp tục vào <span style="color:#DC2626;">*</span> <span style="font-weight:400; color:#9CA3AF;">— giờ VN</span></label>
                    <input wire:model="freezeUntil" type="datetime-local" class="input" style="width:100%;">
                    @error('freezeUntil') <p style="color:#DC2626; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex gap-2 mt-5">
                <button wire:click="saveFreeze" class="btn btn-primary" style="flex:1;">Tạm dừng</button>
                <button wire:click="$set('showFreezeModal', false)" class="btn btn-ghost" style="flex:1;">Hủy</button>
            </div>
        </div>
    </div>
    @endif
</div>
