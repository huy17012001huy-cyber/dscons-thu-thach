<div style="max-width:980px;margin:0 auto;padding:32px 18px 64px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin-bottom:22px;">
        <div>
            <p style="margin:0 0 7px;color:#1F77BE;font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;">Quản trị community</p>
            <h1 style="margin:0;color:#123B59;font-size:clamp(25px,4vw,34px);letter-spacing:-.035em;">Thành viên {{ $community->name }}</h1>
            <p style="margin:9px 0 0;color:#61798A;line-height:1.6;">Gán Moderator cho việc kiểm duyệt. Chỉ Owner được gán hoặc thu hồi Admin.</p>
        </div>
        <a href="{{ community_route('manage') }}" class="btn btn-ghost" style="text-decoration:none;white-space:nowrap;">← Quản trị community</a>
    </div>

    <section style="overflow:hidden;border:1px solid #C9DEE8;border-radius:18px;background:#fff;box-shadow:0 8px 24px rgba(18,59,89,.06);">
        @forelse($members as $member)
            @php($role = $member->pivot->role)
            <div style="display:flex;align-items:center;gap:12px;padding:14px 18px;border-bottom:1px solid #E7EEF1;">
                <img src="{{ $member->avatar_url }}" alt="" width="42" height="42" style="flex:0 0 auto;border-radius:50%;object-fit:cover;border:1px solid #D7E5EE;">
                <div style="min-width:0;flex:1;">
                    <strong style="display:block;overflow:hidden;color:#183B55;font-size:14px;text-overflow:ellipsis;white-space:nowrap;">{{ $member->name }}</strong>
                    <span style="display:block;margin-top:3px;color:#61798A;font-size:12px;">@{{ $member->username ?: $member->id }}</span>
                </div>
                <span style="min-width:82px;color:#125A96;font-size:12px;font-weight:800;text-align:center;">{{ ['owner' => 'Owner', 'admin' => 'Admin', 'moderator' => 'Moderator', 'member' => 'Member'][$role] ?? $role }}</span>
                @if(!$member->is_admin && $role !== 'owner')
                    <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;">
                        @if($role !== 'moderator')
                            <button type="button" wire:click="changeRole({{ $member->id }}, 'moderator')" class="btn btn-ghost" style="min-height:34px;padding:5px 9px;font-size:11px;">Gán Moderator</button>
                        @else
                            <button type="button" wire:click="changeRole({{ $member->id }}, 'member')" class="btn btn-ghost" style="min-height:34px;padding:5px 9px;font-size:11px;">Hạ về Member</button>
                        @endif
                        @if($community->owner_id === auth()->id())
                            @if($role !== 'admin')
                                <button type="button" wire:click="changeRole({{ $member->id }}, 'admin')" class="btn btn-primary" style="min-height:34px;padding:5px 9px;font-size:11px;">Gán Admin</button>
                            @else
                                <button type="button" wire:click="changeRole({{ $member->id }}, 'member')" class="btn btn-ghost" style="min-height:34px;padding:5px 9px;font-size:11px;">Thu hồi Admin</button>
                            @endif
                            <button type="button" wire:click="transferOwnership({{ $member->id }})" wire:confirm="Chuyển quyền Owner cho thành viên này? Bạn sẽ trở thành Admin." class="btn btn-ghost" style="min-height:34px;padding:5px 9px;font-size:11px;">Chuyển Owner</button>
                        @endif
                    </div>
                @else
                    <span style="color:#61798A;font-size:11px;">Bảo vệ</span>
                @endif
            </div>
        @empty
            <p style="padding:28px 18px;color:#61798A;">Community chưa có thành viên.</p>
        @endforelse
    </section>

    <div style="margin-top:18px;">{{ $members->links() }}</div>
</div>
