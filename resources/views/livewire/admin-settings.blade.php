<div>
    {{-- ── Cấu hình xác minh email ─────────────────────────────── --}}
    <div class="card mb-4" style="max-width:720px; margin-left:auto; margin-right:auto;">
        <div class="mb-4">
            <h1 style="font-size:1.25rem; font-weight:700; color:#1A1A1A;">✉️ Cài đặt email</h1>
            <p style="font-size:0.8rem; color:#5C5C66; margin-top:0.2rem;">Cấu hình người gửi và phương thức gửi mail</p>
        </div>

        <form wire:submit="save" class="flex flex-col gap-4">
            {{-- Người gửi --}}
            <div class="flex gap-3">
                <div style="flex:1;">
                    <label style="font-size:0.75rem; color:#5C5C66; display:block; margin-bottom:0.3rem;">Tên người gửi</label>
                    <input wire:model="mailFromName" type="text" class="input" placeholder="DSCons">
                    @error('mailFromName') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                </div>
                <div style="flex:1;">
                    <label style="font-size:0.75rem; color:#5C5C66; display:block; margin-bottom:0.3rem;">Địa chỉ người gửi <span style="color:#991B1B;">*</span></label>
                    <input wire:model="mailFromAddress" type="email" class="input" placeholder="no-reply@dscons.vn">
                    @error('mailFromAddress') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                </div>
            </div>
            {{-- Phương thức gửi mail + API key --}}
            <div x-data>
                <label style="font-size:0.75rem; color:#5C5C66; display:block; margin-bottom:0.3rem;">Phương thức gửi mail <span style="color:#991B1B;">*</span></label>
                <select wire:model="mailDriver" class="input">
                    <option value="log">Ghi vào log (chỉ thử nghiệm — không gửi thật)</option>
                    <option value="resend">Gửi thật qua Resend</option>
                    <option value="brevo">Gửi thật qua Brevo</option>
                </select>
                @error('mailDriver') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror

                {{-- Resend API key — luôn hiện để dễ nhập/lưu, kể cả khi đang ở chế độ log --}}
                <div style="margin-top:0.85rem;">
                    <label style="font-size:0.75rem; color:#5C5C66; display:block; margin-bottom:0.3rem;">
                        Resend API key
                        @if($resendApiKeySet)<span style="color:#d17856;">— đã lưu, để trống nếu giữ nguyên</span>@else <span style="color:#8A8A94;">— chỉ cần khi dùng Resend</span>@endif
                    </label>
                    <input wire:model="resendApiKey" type="password" class="input" autocomplete="new-password"
                        placeholder="{{ $resendApiKeySet ? '•••••••••••••••• (đã lưu)' : 're_xxxxxxxxxxxxxxxxxxxx' }}">
                    @error('resendApiKey') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                    <p style="font-size:0.72rem; color:#8A8A94; margin-top:0.35rem;">
                        Lấy key tại <code style="background:#EDE9FE; padding:0.05rem 0.3rem; border-radius:0.25rem;">resend.com/api-keys</code>.
                        Domain của địa chỉ người gửi ở trên phải được xác minh trong tài khoản Resend thì email mới gửi được.
                    </p>
                </div>

                {{-- Brevo API key — luôn hiện để dễ nhập/lưu --}}
                <div style="margin-top:0.85rem;">
                    <label style="font-size:0.75rem; color:#5C5C66; display:block; margin-bottom:0.3rem;">
                        Brevo API key
                        @if($brevoApiKeySet)<span style="color:#d17856;">— đã lưu, để trống nếu giữ nguyên</span>@else <span style="color:#8A8A94;">— chỉ cần khi dùng Brevo</span>@endif
                    </label>
                    <input wire:model="brevoApiKey" type="password" class="input" autocomplete="new-password"
                        placeholder="{{ $brevoApiKeySet ? '•••••••••••••••• (đã lưu)' : 'xkeysib-xxxxxxxxxxxxxxxxxxxx' }}">
                    @error('brevoApiKey') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
                    <p style="font-size:0.72rem; color:#8A8A94; margin-top:0.35rem;">
                        Lấy key tại <code style="background:#EDE9FE; padding:0.05rem 0.3rem; border-radius:0.25rem;">app.brevo.com → SMTP & API → API Keys</code>.
                        Địa chỉ người gửi ở trên phải là sender đã xác minh trong tài khoản Brevo.
                    </p>
                </div>
            </div>

            <div>
                <button type="submit" class="btn btn-primary" style="font-size:0.875rem;" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Lưu cấu hình</span>
                    <span wire:loading wire:target="save">Đang lưu...</span>
                </button>
            </div>
        </form>
    </div>

    {{-- ── Gửi email thử ──────────────────────────────────────── --}}
    <div class="card" style="max-width:720px; margin-left:auto; margin-right:auto;">
        <div class="mb-3">
            <h2 style="font-size:1rem; font-weight:700; color:#1A1A1A;">🧪 Gửi email thử</h2>
            <p style="font-size:0.78rem; color:#5C5C66; margin-top:0.2rem;">
                Gửi một email thử để kiểm tra cấu hình gửi mail. Đang dùng:
                <strong style="color:#1A1A1A;">{{ ['resend' => 'Resend (gửi thật)', 'brevo' => 'Brevo (gửi thật)'][$mailDriver] ?? 'log (thử nghiệm)' }}</strong>@if($mailDriver === 'log') — email ghi vào storage/logs/laravel.log, không gửi thật. Chọn "Gửi thật qua Resend" hoặc "Gửi thật qua Brevo" rồi lưu cấu hình trước khi gửi thử.@endif
            </p>
        </div>

        <div class="flex gap-3 items-start">
            <div style="flex:1;">
                <input wire:model="testEmail" type="email" class="input" placeholder="email-cua-ban@gmail.com">
                @error('testEmail') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
            </div>
            <button wire:click="sendTest" class="btn btn-primary" style="font-size:0.875rem; flex-shrink:0;" wire:loading.attr="disabled" wire:target="sendTest">
                <span wire:loading.remove wire:target="sendTest">Gửi thử</span>
                <span wire:loading wire:target="sendTest">Đang gửi...</span>
            </button>
        </div>
    </div>

    {{-- ── Webhook tạo thành viên ─────────────────────────────── --}}
    <div class="card mt-4" style="max-width:720px; margin-left:auto; margin-right:auto;"
         x-data="{ copied: '' }">
        <div class="mb-3">
            <h2 style="font-size:1rem; font-weight:700; color:#1A1A1A;">🔗 Webhook tạo thành viên</h2>
            <p style="font-size:0.78rem; color:#5C5C66; margin-top:0.2rem;">
                Cho phép hệ thống bên thứ 3 (form bán hàng, bot, automation…) tự tạo tài khoản thành viên.
                Bên gửi đính kèm secret bên dưới qua header <code style="background:#EDE9FE; padding:0.05rem 0.3rem; border-radius:0.25rem;">Authorization: Bearer &lt;secret&gt;</code>
                hoặc thêm trường <code style="background:#EDE9FE; padding:0.05rem 0.3rem; border-radius:0.25rem;">secret</code> ngay trong body JSON.
            </p>
        </div>

        {{-- Endpoint URL --}}
        <div class="mb-3">
            <label style="font-size:0.75rem; color:#5C5C66; display:block; margin-bottom:0.3rem;">Địa chỉ webhook (POST)</label>
            <div class="flex gap-2 items-stretch">
                <input type="text" class="input" readonly value="{{ $webhookUrl }}" style="flex:1; font-family:monospace; font-size:0.8rem; background:#F7F5F3;">
                <button type="button" class="btn btn-ghost" style="font-size:0.8rem; flex-shrink:0;"
                    @click="navigator.clipboard.writeText('{{ $webhookUrl }}'); copied='url'; setTimeout(() => copied='', 1500)">
                    <span x-text="copied === 'url' ? 'Đã chép ✓' : 'Chép'"></span>
                </button>
            </div>
        </div>

        {{-- Secret --}}
        <div class="mb-3">
            <label style="font-size:0.75rem; color:#5C5C66; display:block; margin-bottom:0.3rem;">Secret (API key)</label>
            <div class="flex gap-2 items-stretch">
                <input wire:model="webhookSecret" type="text" class="input" placeholder="Chưa có secret — bấm “Tạo key mới”"
                    style="flex:1; font-family:monospace; font-size:0.8rem;">
                <button type="button" class="btn btn-ghost" style="font-size:0.8rem; flex-shrink:0;"
                    x-show="$wire.webhookSecret"
                    @click="navigator.clipboard.writeText($wire.webhookSecret); copied='secret'; setTimeout(() => copied='', 1500)">
                    <span x-text="copied === 'secret' ? 'Đã chép ✓' : 'Chép'"></span>
                </button>
            </div>
            @error('webhookSecret') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
            <p style="font-size:0.72rem; color:#8A8A94; margin-top:0.35rem;">
                Tạo key mới sẽ làm các bên đang dùng key cũ ngừng hoạt động cho tới khi cập nhật key mới.
            </p>
        </div>

        <div class="flex gap-2">
            <button wire:click="generateWebhookSecret" type="button" class="btn btn-ghost" style="font-size:0.875rem;">
                Tạo key mới
            </button>
            <button wire:click="saveWebhook" class="btn btn-primary" style="font-size:0.875rem;" wire:loading.attr="disabled" wire:target="saveWebhook">
                <span wire:loading.remove wire:target="saveWebhook">Lưu webhook</span>
                <span wire:loading wire:target="saveWebhook">Đang lưu...</span>
            </button>
        </div>

        {{-- Tham số gửi kèm --}}
        <div style="margin-top:1rem; border-top:1px solid #E1E1E1; padding-top:0.85rem;">
            <p style="font-size:0.75rem; color:#5C5C66; margin-bottom:0.4rem;">Body (JSON) bên thứ 3 cần gửi:</p>
            <pre style="background:#F7F5F3; border:1px solid #E1E1E1; border-radius:0.5rem; padding:0.75rem; font-size:0.75rem; overflow-x:auto; margin:0;"><code>{
  "name": "Nguyễn Văn A",        // bắt buộc
  "email": "a@gmail.com",        // bắt buộc
  "secret": "&lt;secret&gt;",          // nếu không gắn ở header thì để đây
  "source": "fluentcrm",         // tùy chọn — nguồn tạo tài khoản
  "password": "matkhau123",      // tùy chọn — bỏ trống hệ thống tự sinh
  "referral": "username_gioi_thieu" // tùy chọn
}</code></pre>
            <p style="font-size:0.72rem; color:#8A8A94; margin-top:0.4rem;">
                Tài khoản tạo qua webhook được kích hoạt ngay (membership vĩnh viễn, không cần xác minh email).
            </p>
        </div>
    </div>
    {{-- ─── SePay: thanh toán chuyển khoản ───────────────────────── --}}
    <div class="card mt-4" style="max-width:720px; margin-left:auto; margin-right:auto;"
         x-data="{ copiedSepay: '' }">
        <div class="mb-3">
            <h2 style="font-size:1rem; font-weight:700; color:#1A1A1A;">▣ SePay — thanh toán chuyển khoản</h2>
            <p style="font-size:0.78rem; color:#5C5C66; margin-top:0.2rem;">
                Dùng cho phí tham gia thử thách, khóa học, sản phẩm số và membership. Nhập số tài khoản để
                hệ thống sinh mã QR VietQR, và dán API Key của SePay để webhook xác nhận tiền vào tự động.
            </p>
        </div>

        <div class="flex gap-3 mb-3" style="flex-wrap:wrap;">
            <div style="flex:1; min-width:200px;">
                <label style="font-size:0.75rem; color:#5C5C66; display:block; margin-bottom:0.3rem;">Số tài khoản nhận tiền</label>
                <input wire:model="sepayBankAccount" type="text" class="input" placeholder="0123456789"
                       style="width:100%; font-family:monospace;">
                @error('sepayBankAccount') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
            </div>
            <div style="flex:1; min-width:160px;">
                <label style="font-size:0.75rem; color:#5C5C66; display:block; margin-bottom:0.3rem;">Ngân hàng</label>
                <input wire:model="sepayBankName" type="text" class="input" placeholder="MBBank" style="width:100%;">
                @error('sepayBankName') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Endpoint webhook để dán vào dashboard SePay --}}
        <div class="mb-3">
            <label style="font-size:0.75rem; color:#5C5C66; display:block; margin-bottom:0.3rem;">Địa chỉ webhook (dán vào dashboard SePay)</label>
            <div class="flex gap-2 items-stretch">
                <input type="text" class="input" readonly value="{{ $sepayWebhookUrl }}" style="flex:1; font-family:monospace; font-size:0.8rem; background:#F7F5F3;">
                <button type="button" class="btn btn-ghost" style="font-size:0.8rem; flex-shrink:0;"
                    @click="navigator.clipboard.writeText('{{ $sepayWebhookUrl }}'); copiedSepay='url'; setTimeout(() => copiedSepay='', 1500)">
                    <span x-text="copiedSepay === 'url' ? 'Đã chép ✓' : 'Chép'"></span>
                </button>
            </div>
        </div>

        {{-- API Key (write-only) --}}
        <div class="mb-3">
            <label style="font-size:0.75rem; color:#5C5C66; display:block; margin-bottom:0.3rem;">
                API Key webhook
                @if($sepayWebhookTokenSet)
                <span style="color:#059669; font-weight:600;">· đã lưu</span>
                @else
                <span style="color:#991B1B; font-weight:600;">· chưa có — webhook sẽ từ chối mọi request</span>
                @endif
            </label>
            <input wire:model="sepayWebhookToken" type="password" class="input" autocomplete="new-password"
                   placeholder="{{ $sepayWebhookTokenSet ? 'Để trống = giữ key hiện tại' : 'Dán API Key từ dashboard SePay' }}"
                   style="width:100%; font-family:monospace;">
            @error('sepayWebhookToken') <p style="color:#991B1B; font-size:0.7rem; margin-top:0.2rem;">{{ $message }}</p> @enderror
            <p style="font-size:0.72rem; color:#8A8A94; margin-top:0.35rem;">
                Trong dashboard SePay, cấu hình webhook gửi header
                <code style="background:#EDE9FE; padding:0.05rem 0.3rem; border-radius:0.25rem;">Authorization: Apikey &lt;key&gt;</code>.
                Key này chỉ ghi vào, không bao giờ hiển thị lại.
            </p>
        </div>

        <button wire:click="saveSepay" wire:loading.attr="disabled" wire:target="saveSepay" class="btn btn-primary" style="font-size:0.875rem;">
            <span wire:loading.remove wire:target="saveSepay">Lưu cấu hình SePay</span>
            <span wire:loading wire:target="saveSepay">Đang lưu...</span>
        </button>

        {{-- Nội dung chuyển khoản hệ thống nhận diện --}}
        <div style="margin-top:1rem; border-top:1px solid #E1E1E1; padding-top:0.85rem;">
            <p style="font-size:0.75rem; color:#5C5C66; margin-bottom:0.4rem;">Mã nội dung chuyển khoản hệ thống tự nhận diện:</p>
            <pre style="background:#F7F5F3; border:1px solid #E1E1E1; border-radius:0.5rem; padding:0.75rem; font-size:0.75rem; overflow-x:auto; margin:0;"><code>CHAL{id_thu_thach}U{id_user}   → phí tham gia thử thách
COURSE{id_khoa_hoc}U{id_user}  → mua khóa học
PROD{id_san_pham}U{id_user}    → mua sản phẩm số
MEM{so_tuan}WU{id_user}        → gia hạn membership</code></pre>
            <p style="font-size:0.72rem; color:#8A8A94; margin-top:0.4rem;">
                Mã QR tự sinh sẵn nội dung này, member chỉ cần quét. Nhận đủ tiền → hệ thống duyệt ngay, không cần admin xác nhận tay.
            </p>
        </div>
    </div>
</div>
