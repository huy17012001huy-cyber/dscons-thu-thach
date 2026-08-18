<div>
    <h1 style="font-size:1.25rem; font-weight:800; color:#1A1A1A; margin-bottom:1rem;">▦ Quản lý khóa học</h1>

    <div class="flex flex-col gap-3">
        @foreach($courses as $course)
        <div class="card flex items-center gap-4">
            <div style="flex:1;">
                <div class="flex items-center gap-2 mb-1">
                    <h3 style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">{{ $course->title }}</h3>
                    <span class="badge badge-pillar-{{ $course->pillar }}" style="font-size:0.65rem;">{{ ucfirst($course->pillar) }}</span>
                    @if($course->is_published)
                    <span class="badge" style="background:#D1FAE5; color:#065F46; font-size:0.65rem;">Published</span>
                    @else
                    <span class="badge" style="background:#EEECE9; color:#5C5C66; font-size:0.65rem;">Draft</span>
                    @endif
                </div>
                <p style="font-size:0.75rem; color:#5C5C66;">{{ $course->modules_count }} modules · {{ $course->enrollments_count }} học viên · +{{ $course->xp_reward }} XP</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.courses.build', $course->id) }}" class="btn btn-secondary" style="font-size:0.75rem; padding:0.3rem 0.625rem;">Xây dựng</a>
                <button wire:click="togglePublish({{ $course->id }})" class="btn {{ $course->is_published ? 'btn-ghost' : 'btn-success' }}" style="font-size:0.75rem; padding:0.3rem 0.625rem;">
                    {{ $course->is_published ? 'Ẩn' : 'Xuất bản' }}
                </button>
                <button wire:click="deleteCourse({{ $course->id }})" wire:confirm="Xóa khóa học này?" class="btn btn-danger" style="font-size:0.75rem; padding:0.3rem 0.625rem;">Xóa</button>
            </div>
        </div>
        @endforeach
    </div>

    @if($courses->isEmpty())
    <div class="card text-center py-8">
        <p style="color:#5C5C66;">Chưa có khóa học nào.</p>
    </div>
    @endif
</div>
