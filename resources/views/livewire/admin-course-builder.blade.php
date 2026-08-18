<div>
    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('admin.courses') }}" style="font-size:0.8rem; color:#5C5C66;">← Khóa học</a>
        <h1 style="font-size:1.1rem; font-weight:800; color:#1A1A1A;">🔧 {{ $course->title }}</h1>
    </div>

    {{-- Modules --}}
    @foreach($course->modules->sortBy('order_index') as $module)
    <div class="card mb-4">
        <div class="flex items-center justify-between mb-3">
            <h2 style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">{{ $module->title }}</h2>
            <div class="flex gap-2">
                <button wire:click="$set('addLessonToModule', {{ $module->id }})" class="btn btn-secondary" style="font-size:0.7rem; padding:0.25rem 0.5rem;">+ Bài học</button>
                <button wire:click="deleteModule({{ $module->id }})" wire:confirm="Xóa module này?" class="btn btn-danger" style="font-size:0.7rem; padding:0.25rem 0.5rem;">Xóa</button>
            </div>
        </div>

        {{-- Lessons in module --}}
        @foreach($module->lessons->sortBy('order_index') as $lesson)
        <div style="margin-left:1rem; padding:0.625rem; border-left:2px solid {{ $lesson->lesson_type === 'practice' ? '#059669' : '#d17856' }}; margin-bottom:0.5rem; background:#F7F5F3; border-radius:0 0.375rem 0.375rem 0;">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span style="font-size:0.65rem; font-weight:700; color:#5C5C66;">{{ $lesson->order_index + 1 }}.</span>
                    <span style="font-size:0.8rem; font-weight:600; color:#1A1A1A;">{{ $lesson->title }}</span>
                    <span class="badge" style="font-size:0.6rem; {{ $lesson->lesson_type === 'practice' ? 'background:#D1FAE5; color:#065F46;' : 'background:#E8F5E9; color:#1B5E20;' }}">{{ $lesson->lesson_type }}</span>
                    @if($lesson->is_locked_by_default)
                    <span style="font-size:0.65rem; color:#5C5C66;">▪</span>
                    @endif
                    <span style="font-size:0.65rem; color:#d17856;">+{{ $lesson->xp_reward }}XP</span>
                </div>
                <div class="flex gap-1">
                    <button wire:click="$set('addTaskToLesson', {{ $lesson->id }})" class="btn btn-ghost" style="font-size:0.65rem; padding:0.2rem 0.4rem;">+ Task</button>
                    <button wire:click="deleteLesson({{ $lesson->id }})" wire:confirm="Xóa bài học?" class="btn btn-danger" style="font-size:0.65rem; padding:0.2rem 0.4rem;">×</button>
                </div>
            </div>

            {{-- Tasks in lesson --}}
            @if($lesson->tasks->count())
            <div style="margin-top:0.5rem; margin-left:1rem;">
                @foreach($lesson->tasks->sortBy('order_index') as $task)
                <div class="flex items-center justify-between py-1" style="{{ !$loop->last ? 'border-bottom:1px solid #E1E1E1;' : '' }}">
                    <div class="flex items-center gap-2">
                        <span style="font-size:0.6rem; color:#5C5C66;">{{ $task->order_index + 1 }})</span>
                        <span style="font-size:0.75rem; color:#2E2E2E;">{{ $task->title }}</span>
                        <span class="badge" style="font-size:0.55rem; background:#EEECE9; color:#5C5C66;">{{ $task->type }}</span>
                        @if($task->is_required)
                        <span style="font-size:0.55rem; color:#991B1B;">*</span>
                        @endif
                    </div>
                    <button wire:click="deleteTask({{ $task->id }})" class="btn btn-ghost" style="font-size:0.6rem; padding:0.1rem 0.3rem; color:#991B1B;">×</button>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Add task form --}}
            @if($addTaskToLesson === $lesson->id)
            <div style="margin-top:0.5rem; margin-left:1rem; padding:0.5rem; background:#FFFFFF; border-radius:0.375rem; border:1px solid #E1E1E1;">
                <div class="grid gap-2" style="grid-template-columns: 1fr auto auto auto;">
                    <input wire:model="taskTitle" class="input" placeholder="Tên nhiệm vụ" style="font-size:0.75rem;">
                    <select wire:model="taskType" class="input" style="font-size:0.75rem; width:auto;">
                        <option value="text">Text</option>
                        <option value="link">Link</option>
                        <option value="file">File</option>
                    </select>
                    <label class="flex items-center gap-1" style="font-size:0.7rem; color:#5C5C66;">
                        <input wire:model="taskRequired" type="checkbox"> Bắt buộc
                    </label>
                    <button wire:click="addTask" class="btn btn-primary" style="font-size:0.7rem; padding:0.25rem 0.5rem;">Thêm</button>
                </div>
                <input wire:model="taskDescription" class="input mt-1" placeholder="Mô tả (tùy chọn)" style="font-size:0.75rem;">
                @error('taskTitle') <p style="color:#991B1B; font-size:0.7rem;">{{ $message }}</p> @enderror
            </div>
            @endif
        </div>
        @endforeach

        {{-- Add lesson form --}}
        @if($addLessonToModule === $module->id)
        <div style="margin-left:1rem; padding:0.625rem; background:#FFFBEB; border:1px solid #FDE68A; border-radius:0.375rem;">
            <div class="grid gap-2" style="grid-template-columns: 1fr auto auto auto auto;">
                <input wire:model="lessonTitle" class="input" placeholder="Tên bài học" style="font-size:0.75rem;">
                <select wire:model="lessonType" class="input" style="font-size:0.75rem; width:auto;">
                    <option value="lecture">Bài giảng</option>
                    <option value="practice">Thực hành</option>
                </select>
                <input wire:model="lessonXp" type="number" class="input" style="font-size:0.75rem; width:60px;" placeholder="XP">
                <label class="flex items-center gap-1" style="font-size:0.7rem; color:#5C5C66;">
                    <input wire:model="lessonLocked" type="checkbox"> Khoá
                </label>
                <button wire:click="addLesson" class="btn btn-primary" style="font-size:0.7rem; padding:0.25rem 0.5rem;">Thêm</button>
            </div>
            @error('lessonTitle') <p style="color:#991B1B; font-size:0.7rem;">{{ $message }}</p> @enderror
        </div>
        @endif
    </div>
    @endforeach

    {{-- Add module --}}
    @if($showAddModule)
    <div class="card mb-4" style="background:#FFFBEB; border-color:#FDE68A;">
        <div class="flex gap-2">
            <input wire:model="moduleName" class="input" placeholder="Tên module (VD: Tuần 1 — Nền tảng)" style="font-size:0.8rem;">
            <button wire:click="addModule" class="btn btn-primary" style="font-size:0.8rem;">Thêm</button>
            <button wire:click="$set('showAddModule', false)" class="btn btn-ghost" style="font-size:0.8rem;">Hủy</button>
        </div>
        @error('moduleName') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
    </div>
    @else
    <button wire:click="$set('showAddModule', true)" class="btn btn-secondary w-full justify-center" style="font-size:0.8rem;">+ Thêm Module</button>
    @endif
</div>
