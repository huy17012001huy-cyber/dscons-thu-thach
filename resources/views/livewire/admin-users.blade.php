<div class="admin-users-page">
<style>
    .admin-users-page { max-width: 1180px; margin: 0 auto; }
    .admin-users-page .admin-table-scroll { overflow-x: auto; padding: 0; }
    .admin-users-page .admin-users-table { min-width: 820px; }
    .admin-users-page .admin-users-table th { background: #F7FAFC; }
    .admin-users-page .admin-users-table th:first-child { border-top-left-radius: 15px; }
    .admin-users-page .admin-users-table th:last-child { border-top-right-radius: 15px; }
    .admin-users-page .admin-users-table tr:hover td { background: #FBFDFE; }
    .admin-users-page .admin-users-table .btn { min-height: 32px; }
    @media (max-width: 640px) { .admin-users-page .admin-toolbar .input { max-width: none !important; } }
</style>

    <h1 style="font-size:1.25rem; font-weight:800; color:#1A1A1A; margin-bottom:1rem;">■ Quản lý người dùng</h1>

    <div class="admin-toolbar flex gap-2 flex-wrap mb-4">
        <input wire:model.live.debounce.300ms="search" type="search" class="input" placeholder="Tìm theo tên, email, username..." style="max-width:400px;">
        <button type="button" wire:click="openCreateModal" class="btn btn-primary">
            + Tạo thành viên
        </button>
        <button type="button" wire:click="exportCsv" wire:loading.attr="disabled" wire:target="exportCsv" class="btn btn-secondary">
            Xuất CSV
        </button>
    </div>

    {{-- ─── Modal tạo thành viên mới ─────────────────────────── --}}
    @if($showCreateModal)
    <div class="admin-modal-backdrop" x-data x-on:keydown.escape.window="$wire.set('showCreateModal', false)" role="presentation" style="position:fixed; inset:0; background:rgba(16,42,59,.52); z-index:100; display:flex; align-items:center; justify-content:center; padding:1rem;"
         wire:click.self="$set('showCreateModal', false)">
        <div class="card admin-modal-card" role="dialog" aria-modal="true" aria-labelledby="create-member-title" style="background:#fff; max-width:440px; width:100%; max-height:90vh; overflow-y:auto;" wire:click.stop>
            <h2 id="create-member-title" style="font-size:1.05rem; font-weight:800; color:#1A1A1A; margin-bottom:1rem;">Tạo thành viên mới</h2>

            <form wire:submit="createUser" style="display:flex; flex-direction:column; gap:0.85rem;">
                <div>
                    <label for="new-member-name" style="display:block; font-size:0.75rem; font-weight:600; color:#5C5C66; margin-bottom:0.3rem;">Họ tên</label>
                    <input id="new-member-name" wire:model="newName" type="text" class="input" style="width:100%;" placeholder="Nguyễn Văn A">
                    @error('newName')<p style="color:#DC2626; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="new-member-email" style="display:block; font-size:0.75rem; font-weight:600; color:#5C5C66; margin-bottom:0.3rem;">Email</label>
                    <input id="new-member-email" wire:model="newEmail" type="email" class="input" style="width:100%;" placeholder="email@example.com">
                    @error('newEmail')<p style="color:#DC2626; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p>@enderror
                </div>

                <p style="margin:0; padding:0.75rem; border-radius:0.75rem; background:#EFF6FF; color:#1E40AF; font-size:0.78rem; line-height:1.5;">
                    Thành viên sẽ nhận hướng dẫn và đăng nhập bằng Google theo email này. Không tạo hoặc gửi mật khẩu.
                </p>

                <div>
                    <label for="new-member-role" style="display:block; font-size:0.75rem; font-weight:600; color:#5C5C66; margin-bottom:0.3rem;">Vai trò</label>
                    <select id="new-member-role" wire:model="newRole" class="input" style="width:100%;">
                        <option value="member">Thành viên</option>
                        <option value="mod">Moderator</option>
                        <option value="admin">Admin</option>
                    </select>
                    @error('newRole')<p style="color:#DC2626; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-2 justify-end" style="margin-top:0.5rem;">
                    <button type="button" wire:click="$set('showCreateModal', false)" class="btn btn-ghost">Hủy</button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="createUser">
                        <span wire:loading.remove wire:target="createUser">Tạo tài khoản</span>
                        <span wire:loading wire:target="createUser">Đang tạo...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="admin-table-scroll card">
        <table class="admin-users-table" style="width:100%; font-size:0.8rem;">
            <thead>
                <tr style="border-bottom:1px solid #E1E1E1; text-align:left;">
                    <th style="padding:0.5rem; color:#5C5C66; font-weight:600;">Người dùng</th>
                    <th style="padding:0.5rem; color:#5C5C66; font-weight:600;">Level</th>
                    <th style="padding:0.5rem; color:#5C5C66; font-weight:600;">EXP</th>
                    <th style="padding:0.5rem; color:#5C5C66; font-weight:600;">Posts</th>
                    <th style="padding:0.5rem; color:#5C5C66; font-weight:600;">Status</th>
                    <th style="padding:0.5rem; color:#5C5C66; font-weight:600;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr style="border-bottom:1px solid #E1E1E1;">
                    <td style="padding:0.5rem;">
                        <div class="flex items-center gap-2">
                            <img src="{{ $user->avatar_url }}" class="avatar w-7 h-7" alt="">
                            <div>
                                <div class="flex items-center gap-1">
                                    <p style="font-weight:600; color:#1A1A1A;">{{ $user->name }}</p>
                                    <span class="badge badge-class-{{ $user->class_color }}" style="font-size:0.6rem;"><x-icon name="{{ $user->class_icon }}" size="12" /></span>
                                </div>
                                <p style="font-size:0.7rem; color:#5C5C66;">{{ $user->email }}</p>
                                @if($user->source)
                                <span class="badge" style="background:#E1F4F7; color:#125A96; font-size:0.6rem;" title="Nguồn tạo tài khoản">↳ {{ $user->source }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="padding:0.5rem;"><span class="level-badge">Lv.{{ $user->level }}</span></td>
                    <td style="padding:0.5rem; color:#d17856; font-weight:600;">{{ number_format($user->xp) }}</td>
                    <td style="padding:0.5rem;">{{ $user->posts_count }}</td>
                    <td style="padding:0.5rem;">
                        @if($user->is_admin)<span class="badge" style="background:#FEE2E2; color:#991B1B; font-size:0.6rem;">Admin</span>@endif
                        @if($user->is_moderator)<span class="badge" style="background:#DBEAFE; color:#1E40AF; font-size:0.6rem;">Mod</span>@endif
                        @if($user->membership?->status === 'banned')<span class="badge" style="background:#FEE2E2; color:#991B1B; font-size:0.6rem;">Banned</span>@endif
                    </td>
                    <td style="padding:0.5rem;">
                        <div class="flex gap-1 flex-wrap">
                            <button wire:click="toggleAdmin({{ $user->id }})" class="btn btn-ghost" style="font-size:0.65rem; padding:0.2rem 0.4rem;">{{ $user->is_admin ? '- Admin' : '+ Admin' }}</button>
                            <button wire:click="toggleModerator({{ $user->id }})" class="btn btn-ghost" style="font-size:0.65rem; padding:0.2rem 0.4rem;">{{ $user->is_moderator ? '- Mod' : '+ Mod' }}</button>
                            @if($user->membership?->status === 'banned')
                            <button wire:click="unbanUser({{ $user->id }})" class="btn btn-success" style="font-size:0.65rem; padding:0.2rem 0.4rem;">Unban</button>
                            @else
                            <button wire:click="banUser({{ $user->id }})" wire:confirm="Ban user này?" class="btn btn-danger" style="font-size:0.65rem; padding:0.2rem 0.4rem;">Ban</button>
                            @endif
                            @if(!$user->is_admin && $user->id !== auth()->id() && !session('impersonator_id'))
                            <form method="POST" action="{{ route('admin.impersonate.start', $user->id) }}" style="display:inline;" onsubmit="return confirm('Đóng vai ' + {{ \Illuminate\Support\Js::from($user->name) }} + '?')">
                                @csrf
                                <button type="submit" class="btn" style="font-size:0.65rem; padding:0.2rem 0.4rem; background:#7c2d12; color:#fff;" title="Đăng nhập như user này để xem giao diện họ thấy">👁️ Đóng vai</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</div>
