<div class="profile-edit-page">
    <style>
        .profile-edit-page { width:min(100%, 760px); margin:0 auto; }
        .profile-edit-card { padding:1.2rem; border:1px solid #D7E5EE; border-radius:18px; background:#fff; box-shadow:0 6px 18px rgba(18,59,89,.045); }
        .profile-edit-heading { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:1.1rem; }
        .profile-edit-heading h1 { margin:0; color:#123B59; font-size:1.25rem; font-weight:850; letter-spacing:-.03em; }
        .profile-edit-heading p { margin:.3rem 0 0; color:#61798A; font-size:.75rem; line-height:1.5; }
        .profile-edit-avatar { display:flex; align-items:center; gap:.9rem; padding-bottom:1rem; border-bottom:1px solid #E7EEF1; }
        .profile-edit-avatar-wrap { position:relative; width:80px; height:80px; flex:0 0 auto; }
        .profile-edit-avatar-wrap img { width:80px; height:80px; border:3px solid #E1F4F7; border-radius:50%; object-fit:cover; }
        .profile-edit-avatar-button { position:absolute; right:-3px; bottom:-3px; display:grid; place-items:center; width:30px; height:30px; border:2px solid #fff; border-radius:50%; color:#fff; background:#1F77BE; cursor:pointer; }
        .profile-edit-avatar-copy strong { display:block; color:#123B59; font-size:.82rem; font-weight:800; }
        .profile-edit-avatar-copy span { display:block; margin-top:.22rem; color:#61798A; font-size:.7rem; line-height:1.45; }
        .profile-edit-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.8rem; margin-top:1rem; }
        .profile-edit-field { display:grid; gap:.3rem; }
        .profile-edit-field.is-wide { grid-column:1/-1; }
        .profile-edit-field label { display:flex; align-items:center; justify-content:space-between; gap:.5rem; color:#456477; font-size:.7rem; font-weight:800; }
        .profile-edit-field label span { color:#8AA0AC; font-size:.62rem; font-weight:600; }
        .profile-edit-field input, .profile-edit-field textarea { width:100%; padding:.65rem .75rem; border:1px solid #D7E5EE; border-radius:9px; color:#123B59; background:#fff; font:inherit; font-size:.78rem; }
        .profile-edit-field textarea { min-height:110px; resize:vertical; line-height:1.55; }
        .profile-edit-field input:focus, .profile-edit-field textarea:focus { border-color:#1F77BE; outline:3px solid rgba(31,119,190,.14); }
        .profile-edit-field input[readonly] { color:#61798A; background:#F5F8FA; }
        .profile-edit-error { color:#B42318; font-size:.68rem; }
        .profile-edit-actions { display:flex; justify-content:flex-end; gap:.55rem; margin-top:1rem; padding-top:1rem; border-top:1px solid #E7EEF1; }
        .profile-edit-actions button { display:inline-flex; min-height:42px; align-items:center; justify-content:center; gap:.4rem; padding:0 .9rem; border-radius:10px; font-size:.76rem; font-weight:850; cursor:pointer; }
        .profile-edit-cancel { border:1px solid #D7E5EE; color:#456477; background:#fff; }
        .profile-edit-save { border:1px solid #1F77BE; color:#fff; background:#1F77BE; }
        .profile-edit-save:hover { background:#125A96; }
        @media(max-width:640px){ .profile-edit-heading { flex-direction:column; }.profile-edit-grid { grid-template-columns:1fr; }.profile-edit-field.is-wide { grid-column:auto; }.profile-edit-actions button { flex:1; } }
    </style>

    <section class="profile-edit-card">
        <div class="profile-edit-heading">
            <div><h1>Sửa hồ sơ</h1><p>Cập nhật thông tin hiển thị trên hồ sơ cộng đồng của bạn.</p></div>
            <x-icon name="user" size="25" color="#1F77BE" />
        </div>

        <div class="profile-edit-avatar">
            <div class="profile-edit-avatar-wrap">
                <img src="{{ $user->avatar_url }}" alt="Ảnh đại diện của {{ $user->name }}">
                <label class="profile-edit-avatar-button" for="profile-avatar-upload" aria-label="Đổi ảnh đại diện"><x-icon name="image" size="15" color="#fff" /><input id="profile-avatar-upload" type="file" wire:model="avatarUpload" accept="image/jpeg,image/png,image/webp" hidden></label>
            </div>
            <div class="profile-edit-avatar-copy"><strong>Ảnh đại diện</strong><span>JPG, PNG hoặc WEBP · tối đa 2 MB</span><span wire:loading wire:target="avatarUpload">Đang tải ảnh...</span></div>
        </div>
        @error('avatarUpload')<div class="profile-edit-error" style="margin-top:.5rem;">{{ $message }}</div>@enderror

        <div class="profile-edit-grid">
            <div class="profile-edit-field"><label for="profile-email">Email <span>Không thể thay đổi</span></label><input id="profile-email" value="{{ $user->email }}" readonly></div>
            <div class="profile-edit-field"><label for="profile-location">Địa điểm</label><input id="profile-location" wire:model="location" autocomplete="address-level2" placeholder="Hà Nội, Việt Nam">@error('location')<span class="profile-edit-error">{{ $message }}</span>@enderror</div>
            <div class="profile-edit-field"><label for="profile-name">Tên hiển thị</label><input id="profile-name" wire:model="editName" autocomplete="name" placeholder="Tên của bạn">@error('editName')<span class="profile-edit-error">{{ $message }}</span>@enderror</div>
            <div class="profile-edit-field"><label for="profile-username">Handle <span>a-z, 0-9, -, _</span></label><input id="profile-username" wire:model="editUsername" autocomplete="username" spellcheck="false" placeholder="ten-cua-ban">@error('editUsername')<span class="profile-edit-error">{{ $message }}</span>@enderror</div>
            <div class="profile-edit-field is-wide"><label for="profile-bio">Bio <span>{{ mb_strlen($editBio) }}/500</span></label><textarea id="profile-bio" wire:model="editBio" maxlength="500" placeholder="Giới thiệu ngắn về bạn..."></textarea>@error('editBio')<span class="profile-edit-error">{{ $message }}</span>@enderror</div>
        </div>

        <div class="profile-edit-actions">
            <button type="button" class="profile-edit-cancel" wire:click="cancel">Hủy</button>
            <button type="button" class="profile-edit-save" wire:click="save" wire:loading.attr="disabled"><x-icon name="check" size="16" color="#fff" /><span wire:loading.remove wire:target="save">Lưu thay đổi</span><span wire:loading wire:target="save">Đang lưu...</span></button>
        </div>
    </section>
</div>
