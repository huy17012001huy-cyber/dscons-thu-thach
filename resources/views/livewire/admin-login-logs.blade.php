<div>
    <div class="card" style="max-width:1000px; margin-left:auto; margin-right:auto;">
        <div class="mb-4">
            <h1 style="font-size:1.25rem; font-weight:700; color:#1A1A1A;">🔐 Lịch sử đăng nhập</h1>
            <p style="font-size:0.8rem; color:#5C5C66; margin-top:0.2rem;">Gõ IP để xem tất cả tài khoản từ IP đó — gồm cả đang đăng nhập sẵn (session) lẫn lịch sử login.</p>
        </div>

        {{-- Ô tìm kiếm --}}
        <div style="margin-bottom:1rem;">
            <input wire:model.live.debounce.400ms="search" type="text" class="input"
                placeholder="IP, tên, email, fingerprint (16 hex), hoặc device cookie (UUID)...">
        </div>

        {{-- Banner khi tìm thấy match — đếm số tài khoản / fingerprint / cookie khác nhau --}}
        @if($distinctAccounts !== null)
        @php
            $label = match($matchType) {
                'ip'             => 'IP',
                'fingerprint'    => 'Vân tay device',
                'device_cookie'  => 'Device cookie',
                default          => 'Đang xem',
            };
            $alert = $distinctAccounts > 1; // 2+ account khác nhau cùng thứ → đáng nghi
        @endphp
        <div style="background:{{ $alert ? '#FEF3C7' : '#F0F9FF' }}; border:1px solid {{ $alert ? '#FDE68A' : '#BAE6FD' }}; color:#1A1A1A; padding:0.75rem; border-radius:0.5rem; font-size:0.85rem; margin-bottom:1rem;">
            {{ $label }} <strong style="font-family:monospace;">{{ $s }}</strong> có:
            <strong>{{ $distinctAccounts }}</strong> tài khoản
            @if($matchType !== 'fingerprint') · <strong>{{ $distinctFingerprints }}</strong> vân tay device @endif
            @if($matchType !== 'device_cookie') · <strong>{{ $distinctDeviceCookies }}</strong> device cookie @endif
            @if($alert)
                <br>⚠️ Nhiều tài khoản cùng {{ strtolower($label) }} — đáng nghi nick ảo.
                @if($matchType === 'ip' && $distinctDeviceCookies > 0 && $distinctDeviceCookies < $distinctAccounts)
                    Có {{ $distinctAccounts - $distinctDeviceCookies }} tài khoản chia sẻ device cookie → khả năng cao là cùng người.
                @endif
            @endif
        </div>
        @endif

        {{-- ── Đang đăng nhập (sessions) ─────────────────────────── --}}
        <div style="margin-bottom:1.5rem;">
            <h2 style="font-size:0.95rem; font-weight:700; color:#1A1A1A; margin-bottom:0.5rem;">🟢 Đang đăng nhập (session hiện tại)</h2>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:0.8rem;">
                    <thead>
                        <tr style="text-align:left; color:#5C5C66; border-bottom:1px solid #E1E1E1;">
                            <th style="padding:0.5rem 0.6rem;">Hoạt động gần nhất</th>
                            <th style="padding:0.5rem 0.6rem;">Tài khoản</th>
                            <th style="padding:0.5rem 0.6rem;">IP</th>
                            <th style="padding:0.5rem 0.6rem;">Thiết bị</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $sess)
                        @php $online = (time() - (int) $sess->last_activity) < 900; @endphp
                        <tr style="border-bottom:1px solid #F0EFEC;">
                            <td style="padding:0.5rem 0.6rem; color:#5C5C66; white-space:nowrap;">
                                @if($online)<span style="color:#16A34A;">●</span> @endif
                                {{ \Carbon\Carbon::createFromTimestamp((int) $sess->last_activity)->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}
                            </td>
                            <td style="padding:0.5rem 0.6rem;">
                                <span style="font-weight:600; color:#1A1A1A;">{{ $sess->name }}</span>
                                <span style="color:#5C5C66;">@ {{ $sess->username }}</span><br>
                                <span style="color:#8A8A94; font-size:0.72rem;">{{ $sess->email }}</span>
                            </td>
                            <td style="padding:0.5rem 0.6rem;">
                                <button type="button" wire:click="$set('search', '{{ $sess->ip_address }}')"
                                    style="font-family:monospace; color:#d17856; background:none; border:none; cursor:pointer; padding:0;"
                                    title="Lọc theo IP này">{{ $sess->ip_address }}</button>
                            </td>
                            <td style="padding:0.5rem 0.6rem; color:#5C5C66;" title="{{ $sess->user_agent }}">{{ $this->deviceLabel($sess->user_agent) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="padding:1rem; text-align:center; color:#8A8A94;">Không có session nào khớp.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:0.75rem;">{{ $sessions->links() }}</div>
        </div>

        {{-- ── Lịch sử đăng nhập (login_logs) ────────────────────── --}}
        <h2 style="font-size:0.95rem; font-weight:700; color:#1A1A1A; margin-bottom:0.5rem;">📜 Lịch sử đăng nhập mới</h2>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.8rem;">
                <thead>
                    <tr style="text-align:left; color:#5C5C66; border-bottom:1px solid #E1E1E1;">
                        <th style="padding:0.5rem 0.6rem;">Thời gian (VN)</th>
                        <th style="padding:0.5rem 0.6rem;">Tài khoản</th>
                        <th style="padding:0.5rem 0.6rem;">IP</th>
                        <th style="padding:0.5rem 0.6rem;">Thiết bị</th>
                        <th style="padding:0.5rem 0.6rem;" title="Vân tay client-side (canvas+webgl+screen+navigator)">Vân tay</th>
                        <th style="padding:0.5rem 0.6rem;" title="Cookie UUID 5 năm — persist qua logout/login">Cookie</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr style="border-bottom:1px solid #F0EFEC; {{ $log->via_admin ? 'background:#FAFAF9;' : '' }}">
                        <td style="padding:0.5rem 0.6rem; color:#5C5C66; white-space:nowrap;">
                            {{ $log->created_at?->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}
                            @if($log->via_admin)
                                <span style="display:inline-block; margin-left:0.4rem; font-size:0.65rem; background:#E0E7FF; color:#3730A3; padding:0.1rem 0.4rem; border-radius:4px; font-weight:500;" title="Phiên này phát sinh do admin đóng vai, không tính là login thật">đóng vai</span>
                            @endif
                        </td>
                        <td style="padding:0.5rem 0.6rem;">
                            @if($log->user)
                                <span style="font-weight:600; color:#1A1A1A;">{{ $log->user->name }}</span>
                                <span style="color:#5C5C66;">@ {{ $log->user->username }}</span><br>
                                <span style="color:#8A8A94; font-size:0.72rem;">{{ $log->user->email }}</span>
                            @else
                                <span style="color:#8A8A94;">(đã xoá / không rõ)</span>
                            @endif
                        </td>
                        <td style="padding:0.5rem 0.6rem;">
                            <button type="button" wire:click="$set('search', '{{ $log->ip_address }}')"
                                style="font-family:monospace; color:#d17856; background:none; border:none; cursor:pointer; padding:0;"
                                title="Lọc theo IP này">{{ $log->ip_address }}</button>
                        </td>
                        <td style="padding:0.5rem 0.6rem; color:#5C5C66;" title="{{ $log->user_agent }}">{{ $this->deviceLabel($log->user_agent) }}</td>
                        <td style="padding:0.5rem 0.6rem;">
                            @if($log->fingerprint_hash)
                                <button type="button" wire:click="$set('search', '{{ $log->fingerprint_hash }}')"
                                    style="font-family:monospace; font-size:0.72rem; background:#EDE9FE; color:#5B21B6; border:none; padding:0.1rem 0.4rem; border-radius:4px; cursor:pointer;"
                                    title="Click để xem mọi tài khoản từ vân tay này — full: {{ $log->fingerprint_hash }}">{{ substr($log->fingerprint_hash, 0, 8) }}</button>
                            @else
                                <span style="color:#C4C4CC;">—</span>
                            @endif
                        </td>
                        <td style="padding:0.5rem 0.6rem;">
                            @if($log->device_cookie_id)
                                <button type="button" wire:click="$set('search', '{{ $log->device_cookie_id }}')"
                                    style="font-family:monospace; font-size:0.72rem; background:#E0F2FE; color:#075985; border:none; padding:0.1rem 0.4rem; border-radius:4px; cursor:pointer;"
                                    title="Click để xem mọi tài khoản từ cookie này — full: {{ $log->device_cookie_id }}">{{ substr($log->device_cookie_id, 0, 8) }}</button>
                            @else
                                <span style="color:#C4C4CC;">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="padding:1.5rem; text-align:center; color:#8A8A94;">Chưa có lượt đăng nhập nào được ghi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem;">
            {{ $logs->links() }}
        </div>
    </div>
</div>
