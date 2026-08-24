<div class="admin-courses-page">
@php
    $pillars = brand()->pillarProfiles();
@endphp
<style>
    .admin-courses-page { max-width: 1080px; margin: 0 auto; }
    .admin-courses-page .card { border-radius: 16px; border-color: #D7E5EA; }
    .admin-courses-page .course-row { transition: border-color .16s ease, box-shadow .16s ease; }
    .admin-courses-page .course-row:hover { border-color: #8FB9CB; box-shadow: 0 7px 16px rgba(18,59,89,.07); }
    @media (max-width: 640px) { .admin-courses-page .course-row { align-items: flex-start; flex-wrap: wrap; } .admin-courses-page .course-actions { width: 100%; } }
</style>

    <div class="flex items-center justify-between gap-3 mb-4">
        <h1 style="font-size:1.25rem; font-weight:800; color:#1A1A1A; margin:0;">▦ Quản lý khóa học</h1>
        <button wire:click="openCreateCourse" class="btn btn-primary" style="font-size:.8rem;">+ Tạo khóa học</button>
    </div>

    <div class="flex flex-col gap-3">
        @foreach($courses as $course)
        <div class="course-row card flex items-center gap-4">
            <div style="flex:1;">
                <div class="flex items-center gap-2 mb-1">
                    <h3 style="font-size:0.9rem; font-weight:700; color:#1A1A1A;">{{ $course->title }}</h3>
                    <span class="badge badge-pillar-{{ $course->pillar }}" style="font-size:0.65rem;">{{ $pillars[$course->pillar]['name'] ?? ucfirst($course->pillar) }}</span>
                    @if($course->is_published)
                    <span class="badge" style="background:#D1FAE5; color:#065F46; font-size:0.65rem;">Published</span>
                    @else
                    <span class="badge" style="background:#EEECE9; color:#5C5C66; font-size:0.65rem;">Draft</span>
                    @endif
                </div>
                <p style="font-size:0.75rem; color:#5C5C66;">{{ $course->modules_count }} modules · {{ $course->enrollments_count }} học viên · +{{ $course->xp_reward }} XP</p>
            </div>
            <div class="course-actions flex gap-2">
                <button wire:click="openEditCourse({{ $course->id }})" class="btn btn-ghost" style="font-size:0.75rem; padding:0.3rem 0.625rem;">Sửa</button>
                <a href="{{ app()->bound('brand') && request()->routeIs('community.*') ? community_route('manage.courses.build', ['id' => $course->id]) : route('admin.courses.build', $course->id) }}" class="btn btn-secondary" style="font-size:0.75rem; padding:0.3rem 0.625rem;">Xây dựng</a>
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

    @if($showCourseModal)
    <div style="position:fixed;inset:0;z-index:100;display:flex;align-items:center;justify-content:center;padding:1rem;background:rgba(16,42,59,.48);" wire:click.self="$set('showCourseModal', false)">
        <div class="card" role="dialog" aria-modal="true" aria-labelledby="course-editor-title" style="width:100%;max-width:640px;max-height:90dvh;overflow:auto;padding:1.4rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:1rem;"><h2 id="course-editor-title" style="margin:0;font-size:1.05rem;color:#102A3B;">{{ $editingCourseId ? 'Sửa khóa học' : 'Tạo khóa học mới' }}</h2><button type="button" wire:click="$set('showCourseModal', false)" class="btn btn-ghost" aria-label="Đóng">×</button></div>
            <div style="display:grid;gap:.8rem;">
                <label style="display:grid;gap:.3rem;font-size:.78rem;font-weight:700;color:#29485B;">Tên khóa học<input wire:model="courseTitle" class="input" placeholder="Ví dụ: BIM Automation thực chiến"></label>
                @error('courseTitle')<p class="field-error">{{ $message }}</p>@enderror
                <label style="display:grid;gap:.3rem;font-size:.78rem;font-weight:700;color:#29485B;">Mô tả<textarea wire:model="courseDescription" class="input" rows="4" style="resize:vertical;" placeholder="Học viên sẽ đạt được gì sau khóa học?"></textarea></label>
                @error('courseDescription')<p class="field-error">{{ $message }}</p>@enderror
                <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem;">
                    <label style="display:grid;gap:.3rem;font-size:.78rem;font-weight:700;color:#29485B;">Trụ cột<select wire:model="coursePillar" class="input">@foreach($pillars as $key => $pillar)<option value="{{ $key }}">{{ $pillar['name'] }}</option>@endforeach</select></label>
                    <label style="display:grid;gap:.3rem;font-size:.78rem;font-weight:700;color:#29485B;">Độ khó<select wire:model="courseDifficulty" class="input"><option value="basic">Cơ bản</option><option value="advanced">Nâng cao</option><option value="expert">Chuyên gia</option></select></label>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:.75rem;">
                    <label style="display:grid;gap:.3rem;font-size:.78rem;font-weight:700;color:#29485B;">Level tối thiểu<input wire:model="courseMinLevel" type="number" min="1" class="input"></label>
                    <label style="display:grid;gap:.3rem;font-size:.78rem;font-weight:700;color:#29485B;">XP thưởng<input wire:model="courseXpReward" type="number" min="0" class="input"></label>
                    <label style="display:grid;gap:.3rem;font-size:.78rem;font-weight:700;color:#29485B;">AIP thưởng<input wire:model="courseAipReward" type="number" min="0" class="input"></label>
                    <label style="display:grid;gap:.3rem;font-size:.78rem;font-weight:700;color:#29485B;">Giá (VNĐ)<input wire:model="coursePrice" type="number" min="0" class="input" placeholder="0"></label>
                </div>
                <label style="display:grid;gap:.3rem;font-size:.78rem;font-weight:700;color:#29485B;">Ảnh bìa khóa học <span style="font-weight:500;color:#61798A;">(khuyến nghị 16:9)</span><input wire:model="courseThumbnail" type="file" accept="image/*" class="input" style="padding:.45rem;"></label>
                @error('courseThumbnail')<p class="field-error">{{ $message }}</p>@enderror
                @if($existingCourseThumbnail)
                <div style="display:flex;align-items:center;gap:.6rem;padding:.5rem;border:1px solid #D7E5EA;border-radius:.55rem;background:#F8FCFD;"><img src="{{ asset('storage/'.$existingCourseThumbnail) }}" alt="Ảnh bìa hiện tại" style="width:60px;height:38px;object-fit:cover;border-radius:.3rem;"><label style="display:flex;align-items:center;gap:.35rem;color:#A43C35;font-size:.75rem;font-weight:700;"><input wire:model="removeCourseThumbnail" type="checkbox"> Gỡ ảnh khi lưu</label></div>
                @endif
                <label style="display:flex;align-items:center;gap:.45rem;color:#29485B;font-size:.8rem;font-weight:700;"><input wire:model="coursePublished" type="checkbox"> Xuất bản ngay để học viên nhìn thấy</label>
                <label style="display:flex;align-items:center;gap:.45rem;color:#29485B;font-size:.8rem;font-weight:700;"><input wire:model="courseFeatured" type="checkbox"> Nổi bật trên Marketplace</label>
            </div>
            <div style="display:flex;gap:.7rem;margin-top:1.15rem;"><button wire:click="saveCourse" wire:loading.attr="disabled" wire:target="saveCourse,courseThumbnail" class="btn btn-primary" style="flex:1;justify-content:center;"><span wire:loading.remove wire:target="saveCourse">Lưu khóa học</span><span wire:loading wire:target="saveCourse">Đang lưu…</span></button><button type="button" wire:click="$set('showCourseModal', false)" class="btn btn-secondary" style="flex:1;justify-content:center;">Hủy</button></div>
        </div>
    </div>
    @endif
</div>
