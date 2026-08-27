@php($title = 'Kết nối DSCons Revit')
<div style="max-width:620px;margin:36px auto;padding:26px;border:1px solid #B8D7E6;border-radius:20px;background:#fff;box-shadow:0 12px 32px rgba(18,59,89,.09)">
    <p style="margin:0;color:#1F77BE;font-size:.72rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase">DSCons Revit</p>
    <h1 style="margin:9px 0;color:#123B59;font-size:1.45rem">Kết nối Revit với tài khoản DSCons</h1>
    <p style="color:#456477;line-height:1.65">Bạn đang kích hoạt Revit trên <strong>{{ $authorization->device_label ?: 'thiết bị này' }}</strong>@if($authorization->revit_version) · Revit {{ $authorization->revit_version }}@endif. Mỗi tài khoản DSCons chỉ có một thiết bị Revit đang hoạt động.</p>
    @if(session('error'))<div style="margin:16px 0;padding:12px;border-left:3px solid #F39402;background:#FFF8E9;color:#765D31">{{ session('error') }}</div>@endif
    @if(session('success'))<div style="margin:16px 0;padding:12px;border-left:3px solid #1F77BE;background:#EAF6FB;color:#123B59">{{ session('success') }}</div>@else
    <form method="POST" action="{{ route('revit.authorization.approve', ['code' => $code]) }}">@csrf
        <button class="btn btn-primary" type="submit" style="min-height:44px">Xác nhận kết nối thiết bị này</button>
    </form>
    @endif
    <p style="margin:16px 0 0;color:#61798A;font-size:.75rem;line-height:1.55">Không nhập mật khẩu Google vào Revit. Browser này chỉ xác nhận tài khoản đã đăng nhập trên DSCons.</p>
</div>
