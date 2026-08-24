<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMemberMail;
use App\Models\Membership;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegisterWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        // Verify shared secret — ưu tiên secret cấu hình ở /admin/settings (DB),
        // fallback về .env để không vỡ cấu hình cũ.
        $secret = Setting::get('register_webhook_secret') ?: config('services.register_webhook.secret');

        // Chấp nhận secret qua header "Authorization: Bearer <secret>" HOẶC field "secret"
        // trong body — để công cụ không gắn header tùy ý (vd FluentCRM) vẫn dùng được.
        $provided = $request->bearerToken() ?: (string) $request->input('secret', '');

        if (! $secret || ! hash_equals($secret, $provided)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|min:2|max:50',
            'email' => 'required|email',
            'referral' => 'nullable|string|exists:users,username',
            'source' => 'nullable|string|max:80',
        ]);

        // If user already exists, return their info
        $existing = User::where('email', $validated['email'])->first();
        if ($existing) {
            return response()->json([
                'status' => 'existing',
                'user_id' => $existing->id,
                'username' => $existing->username,
                'email' => $existing->email,
            ]);
        }

        // Generate username from name
        $ascii = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', trim($validated['name']));
        $username = preg_replace('/\s+/', '.', $ascii);
        $username = preg_replace('/[^a-z0-9._]/', '', $username);
        $base = $username;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $i++;
        }

        $referrer = null;
        if (! empty($validated['referral'])) {
            $referrer = User::where('username', $validated['referral'])->first();
        }

        $user = User::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'username'    => $username,
            'level'       => 1,
            'xp'          => 0,
            'aip'         => 0,
            'streak'      => 0,
            'referred_by' => $referrer?->id,
            // Luôn đóng dấu nguồn để nhận diện tài khoản tạo qua webhook (auto-duyệt Challenge).
            // Funnel có thể gửi source riêng; nếu không gửi thì mặc định 'webhook'.
            'source'      => filled($validated['source'] ?? null) ? $validated['source'] : 'webhook',
        ]);

        // Đăng ký qua webhook (server-to-server) → coi như đã xác minh email
        $user->markEmailAsVerified();

        Membership::create([
            'user_id'    => $user->id,
            'status'     => 'active',
            'plan'       => 'lifetime',
            'expires_at' => '2099-12-31',
            'referred_by' => $referrer?->id,
        ]);
        $user->brandRoles()->syncWithoutDetaching([
            brand()->id => ['role' => 'member'],
        ]);

        Log::info('Webhook: user registered', [
            'user_id' => $user->id,
            'email' => $user->email,
            'username' => $username,
        ]);

        // Gửi email chào mừng hướng dẫn Google login. Bọc try/catch để lỗi mail
        // không làm hỏng việc tạo tài khoản (đã tạo xong ở trên rồi).
        try {
            Mail::to($user->email)->send(new WelcomeMemberMail(
                name:      $user->name,
                email:     $user->email,
                loginUrl:  route('login'),
                brandName: config('app.name'),
            ));
        } catch (\Throwable $e) {
            Log::warning('Webhook: welcome email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }

        return response()->json([
            'status' => 'created',
            'user_id' => $user->id,
            'username' => $username,
            'email' => $user->email,
        ], 201);
    }
}
