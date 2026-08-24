<div class="admin-topics-page">
<style>
    .admin-topics-page { max-width: 760px; margin: 0 auto; }
    .admin-topics-page .card { border-radius: 18px; border-color: #D7E5EA; }
    .admin-topics-page .input:focus { border-color: #1F77BE; box-shadow: 0 0 0 3px rgba(31,119,190,.14); }
    @media (max-width: 640px) { .admin-topics-page .flex.gap-3 { flex-wrap: wrap; } }
</style>

    <div class="card mb-4" style="max-width:720px; margin-left:auto; margin-right:auto;">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 style="font-size:1.25rem; font-weight:700; color:#1A1A1A;">📌 Quản lý Topics</h1>
                <p style="font-size:0.8rem; color:#5C5C66; margin-top:0.2rem;">Tạo và quản lý các định dạng / chủ đề bài viết</p>
            </div>
            <button wire:click="openCreate" class="btn btn-primary" style="font-size:0.875rem;">
                + Thêm topic
            </button>
        </div>

        {{-- Create / Edit form --}}
        @if($showForm)
        <div style="background:#F7F5F3; border:1px solid #E1E1E1; border-radius:0.625rem; padding:1.25rem; margin-bottom:1.25rem;">
            <h2 style="font-size:0.9rem; font-weight:600; color:#1A1A1A; margin-bottom:1rem;">
                {{ $editingId ? 'Chỉnh sửa topic' : 'Thêm topic mới' }}
            </h2>
            <div class="flex gap-3 mb-3">
                {{-- Emoji --}}
                <div style="width:80px; flex-shrink:0;">
                    <label style="font-size:0.75rem; color:#5C5C66; display:block; margin-bottom:0.3rem;">Emoji</label>
                    <input wire:model="emoji" type="text" class="input" style="text-align:center; font-size:1.2rem;" placeholder="" maxlength="4">
                    @error('emoji') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                </div>
                {{-- Name --}}
                <div style="flex:1;">
                    <label style="font-size:0.75rem; color:#5C5C66; display:block; margin-bottom:0.3rem;">Tên topic <span style="color:#991B1B;">*</span></label>
                    <input wire:model.live="name" type="text" class="input" placeholder="Case Study">
                    @error('name') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                </div>
                {{-- Sort --}}
                <div style="width:80px; flex-shrink:0;">
                    <label style="font-size:0.75rem; color:#5C5C66; display:block; margin-bottom:0.3rem;">Thứ tự</label>
                    <input wire:model="sort_order" type="number" class="input" min="0" placeholder="0">
                    @error('sort_order') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex gap-3 mb-4 items-end">
                {{-- Slug --}}
                <div style="flex:1;">
                    <label style="font-size:0.75rem; color:#5C5C66; display:block; margin-bottom:0.3rem;">Slug <span style="color:#5C5C66; font-style:italic;">(tự động)</span></label>
                    <input wire:model="slug" type="text" class="input" style="font-family:monospace; font-size:0.8rem; color:#5C5C66;" placeholder="case-study">
                    @error('slug') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                </div>
                {{-- Active --}}
                <label class="flex items-center gap-2" style="font-size:0.8rem; color:#2E2E2E; cursor:pointer; padding-bottom:0.625rem;">
                    <input wire:model="is_active" type="checkbox" style="accent-color:#078A48; width:1rem; height:1rem;">
                    Hiển thị
                </label>
            </div>
            <div class="flex gap-2 justify-end">
                <button wire:click="$set('showForm', false)" class="btn btn-ghost" style="font-size:0.8rem;">Hủy</button>
                <button wire:click="save" wire:loading.attr="disabled" wire:target="save" class="btn btn-primary" style="font-size:0.875rem;">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Lưu thay đổi' : 'Tạo topic' }}</span>
                    <span wire:loading wire:target="save">Đang lưu...</span>
                </button>
            </div>
        </div>
        @endif

        {{-- Topics list --}}
        <div style="display:flex; flex-direction:column; gap:0.5rem;">
            @forelse($topics as $topic)
            <div class="flex items-center gap-3 p-3" style="border:1px solid #E1E1E1; border-radius:0.5rem; background:#FFFFFF;">
                <span style="font-size:1.25rem; width:2rem; text-align:center; flex-shrink:0;">{{ $topic->emoji ?? '📌' }}</span>
                <div style="flex:1; min-width:0;">
                    <p style="font-weight:600; color:#1A1A1A; font-size:0.875rem;">{{ $topic->name }}</p>
                    <p style="font-size:0.75rem; color:#5C5C66; font-family:monospace;">{{ $topic->slug }}</p>
                </div>
                <span style="font-size:0.7rem; color:#5C5C66; flex-shrink:0;">#{{ $topic->sort_order }}</span>
                {{-- Active toggle --}}
                <button wire:click="toggleActive({{ $topic->id }})"
                    style="font-size:0.72rem; padding:0.2rem 0.6rem; border-radius:999px; border:1px solid; flex-shrink:0;
                        {{ $topic->is_active
                            ? 'background:#D1FAE5; color:#065F46; border-color:#6EE7B7;'
                            : 'background:#F7F5F3; color:#5C5C66; border-color:#E1E1E1;' }}">
                    {{ $topic->is_active ? 'Đang hiện' : 'Ẩn' }}
                </button>
                {{-- Edit --}}
                <button wire:click="openEdit({{ $topic->id }})"
                    style="font-size:0.75rem; color:#5C5C66; padding:0.25rem 0.5rem; flex-shrink:0;">
                    ✏️
                </button>
                {{-- Delete --}}
                <button wire:click="delete({{ $topic->id }})"
                    wire:confirm="Xóa topic '{{ $topic->name }}'?"
                    style="font-size:0.75rem; color:#EF4444; padding:0.25rem 0.5rem; flex-shrink:0;">
                    🗑️
                </button>
            </div>
            @empty
            <p style="text-align:center; color:#5C5C66; padding:2rem; font-size:0.875rem;">Chưa có topic nào. Thêm mới đi!</p>
            @endforelse
        </div>
    </div>
</div>
