<?php

namespace App\Http\Controllers;

use App\Models\ToolDeviceAuthorization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\RevitTools\Application\ToolLicenseService;

final class RevitDeviceAuthorizationController extends Controller
{
    public function __construct(private readonly ToolLicenseService $licenses) {}

    public function show(string $code): View
    {
        $authorization = $this->authorization($code);
        abort_if(! $authorization || $authorization->expires_at->isPast(), 410, 'Mã kích hoạt đã hết hạn.');

        return view('pages.revit-device-authorization', compact('authorization', 'code'));
    }

    public function approve(Request $request, string $code): RedirectResponse
    {
        $authorization = $this->authorization($code);
        abort_if(! $authorization || $authorization->expires_at->isPast(), 410, 'Mã kích hoạt đã hết hạn.');
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $result = $this->licenses->approveDeviceAuthorization($authorization, $user);
        if (isset($result['blocked'])) {
            return back()->with('error', $result['message']);
        }

        $sessionCredential = $result['token'];
        cache()->put('revit:authorization-credential:'.$authorization->id, $sessionCredential, now()->addMinutes(5));

        return redirect()->route('revit.authorization.show', ['code' => $code])->with('success', 'Đã kết nối Revit. Bạn có thể quay lại Revit; danh sách tool sẽ tự tải.');
    }

    private function authorization(string $code): ?ToolDeviceAuthorization
    {
        return $this->licenses->findAuthorizationByBrowserCode($code);
    }
}
