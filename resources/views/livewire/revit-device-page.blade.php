<div class="revit-device-page">
<style>
.revit-device-page{max-width:920px;margin:0 auto;--blue:#1F77BE;--ink:#123B59;--muted:#61798A}.revit-device-page h1{margin:0;color:var(--ink);font-size:1.45rem}.revit-device-page .intro{margin:.4rem 0 18px;color:var(--muted);font-size:.86rem}.revit-device-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:16px}.revit-panel{padding:20px;border:1px solid #D5E5ED;border-radius:18px;background:#fff;box-shadow:0 5px 16px rgba(18,59,89,.05)}.revit-panel h2{margin:0 0 12px;color:var(--ink);font-size:1rem}.revit-device-status{display:inline-flex;min-height:26px;align-items:center;padding:0 9px;border-radius:999px;background:#EAF6FB;color:#125A96;font-size:.7rem;font-weight:800}.revit-device-status.is-blocked{background:#FFF8E9;color:#9A5B00}.revit-detail{margin:13px 0;padding-top:13px;border-top:1px solid #E5EFF4}.revit-detail dt{color:var(--muted);font-size:.67rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em}.revit-detail dd{margin:4px 0 0;color:#456477;font-size:.83rem}.revit-tool-row{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:11px 0;border-bottom:1px solid #E5EFF4}.revit-tool-row:last-child{border-bottom:0}.revit-tool-row strong{display:block;color:var(--ink);font-size:.83rem}.revit-tool-row small{color:var(--muted);font-size:.7rem}.revit-owned{color:#125A96;font-size:.7rem;font-weight:800}.revit-notice{padding:14px;border-left:3px solid var(--blue);border-radius:9px;background:#EAF6FB;color:#456477;font-size:.8rem;line-height:1.55}.revit-device-page .danger{margin-top:14px;border-color:#F1C8C0;color:#9B3D31;background:#FFF4F2}@media(max-width:720px){.revit-device-grid{grid-template-columns:1fr}}
</style>
<h1>Thiết bị Revit của tôi</h1><p class="intro">DSCons cấp license theo một tài khoản và một thiết bị Revit đang hoạt động.</p>
<div class="revit-device-grid">
 <section class="revit-panel"><h2>Thiết bị đang kích hoạt</h2>
 @if($installation)
  <span class="revit-device-status {{ $installation->status === 'active' ? '' : 'is-blocked' }}">{{ $installation->status === 'active' ? 'Đang hoạt động' : ($installation->status === 'blocked' ? 'Tạm khóa' : 'Đã đăng xuất') }}</span>
  <dl class="revit-detail"><dt>Thiết bị</dt><dd>{{ $installation->device_label ?: 'Thiết bị Revit' }}</dd></dl>
  <dl class="revit-detail"><dt>Phiên bản Revit</dt><dd>{{ $installation->last_revit_version ?: 'Chưa gửi thông tin' }}</dd></dl>
  <dl class="revit-detail"><dt>Lần hoạt động gần nhất</dt><dd>{{ $installation->last_seen_at?->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') ?: 'Chưa có heartbeat' }}</dd></dl>
  @if($installation->blocked_until?->isFuture())<div class="revit-notice">Kích hoạt máy mới tạm dừng đến {{ $installation->blocked_until->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') }}. Bạn vẫn có thể đăng xuất máy hiện tại hoặc liên hệ admin.</div>@endif
  @if($installation->status === 'active')<button wire:click="logoutDevice" wire:confirm="Đăng xuất thiết bị Revit này? Tool sẽ không dùng được trên máy đó cho đến khi kích hoạt lại." class="btn" style="margin-top:16px;border:1px solid #F1C8C0;color:#9B3D31;background:#FFF4F2">Đăng xuất thiết bị</button>@endif
 @else
  <div class="revit-notice">Bạn chưa kích hoạt thiết bị Revit. Mở Revit, vào Ribbon <strong>DSCons</strong> và bấm <strong>Đăng nhập DSCons</strong>.</div>
 @endif
 </section>
 <section class="revit-panel"><h2>Tool bạn đã mua</h2>
 @forelse($tools as $tool)<div class="revit-tool-row"><div><strong>{{ $tool->title }}</strong><small>Hỗ trợ Revit {{ implode(', ', $tool->supported_revit_versions ?: []) ?: 'đang cập nhật' }}</small></div><span class="revit-owned">{{ $tool->purchases->isNotEmpty() ? 'Đã mở' : 'Chưa mua' }}</span></div>@empty <div class="revit-notice">Chưa có tool Revit nào được xuất bản.</div>@endforelse
 </section>
</div>
<div class="revit-notice danger">Không chia sẻ tài khoản DSCons. Khi phát hiện kích hoạt ở thiết bị khác: lần 1 cảnh báo, lần 2 tạm dừng kích hoạt mới, lần 3 license bị khóa để admin kiểm tra.</div>
</div>
