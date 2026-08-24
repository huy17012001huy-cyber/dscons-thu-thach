<?php

namespace App\Providers;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Services\JobDescriptionParser::class, \App\Services\DeterministicJobDescriptionParser::class);
        $this->app->bind(\App\Services\CandidateMatcher::class, \App\Services\DeterministicCandidateMatcher::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('vi');
        Gate::define('admin', fn($user) => $user->isBrandAdmin());
        // Listener RecordLoginLog (app/Listeners) tự được đăng ký qua event auto-discovery

        // Cấu hình gửi mail + cổng thanh toán SePay có thể chỉnh ở /admin/settings (lưu trong
        // bảng settings), ghi đè .env lúc chạy để admin không cần SSH sửa file. Bỏ qua khi chạy
        // console để `php artisan config:cache` không ghi API key vào file cache.
        if (!$this->app->runningInConsole()) {
            try {
                $cfg = Setting::query()
                    ->whereIn('key', [
                        'mail_driver', 'resend_api_key', 'brevo_api_key', 'mail_from_address', 'mail_from_name',
                        'sepay_bank_account', 'sepay_bank_name', 'sepay_webhook_token',
                    ])
                    ->pluck('value', 'key');

                if ($v = $cfg->get('mail_driver'))       config(['mail.default' => $v]);
                if ($v = $cfg->get('resend_api_key'))     config(['services.resend.key' => $v]);
                if ($v = $cfg->get('brevo_api_key'))      config(['services.brevo.key' => $v]);
                if ($v = $cfg->get('mail_from_address'))  config(['mail.from.address' => $v]);
                if ($v = $cfg->get('mail_from_name'))     config(['mail.from.name' => $v]);

                if ($v = $cfg->get('sepay_bank_account'))  config(['services.sepay.bank_account' => $v]);
                if ($v = $cfg->get('sepay_bank_name'))     config(['services.sepay.bank_name' => $v]);
                if ($v = $cfg->get('sepay_webhook_token')) config(['services.sepay.webhook_token' => $v]);
            } catch (\Throwable $e) {
                // Bảng settings chưa sẵn sàng (vd. lúc migrate) → dùng giá trị .env mặc định.
            }
        }

        // Brevo (ex-Sendinblue) — Laravel chưa có sẵn transport, đăng ký qua bridge Symfony.
        // Closure chỉ chạy khi mailer 'brevo' được dùng nên không lỗi khi chưa có key.
        Mail::extend('brevo', function (array $config) {
            $key = config('services.brevo.key');

            return (new BrevoTransportFactory())->create(
                Dsn::fromString('brevo+api://' . urlencode((string) $key) . '@default')
            );
        });

        // Email xác minh — nội dung tiếng Việt, có thể chỉnh ở /admin/settings
        VerifyEmail::toMailUsing(function ($notifiable, string $url): MailMessage {
            $brandName = app()->bound('brand') ? brand()->name : config('app.name');

            $replace = ['{brand}' => $brandName, '{name}' => $notifiable->name];
            $subject = strtr(Setting::get('email_verify_subject', config('verify.subject')), $replace);
            $intro   = strtr(Setting::get('email_verify_intro', config('verify.intro')), $replace);

            $message = (new MailMessage)
                ->subject($subject)
                ->greeting('Chào ' . $notifiable->name . '!')
                ->line($intro)
                ->action('Xác minh email', $url)
                ->line('Liên kết này sẽ hết hạn sau ' . config('auth.verification.expire', 60) . ' phút.')
                ->line('Nếu bạn không tạo tài khoản này, vui lòng bỏ qua email.')
                ->salutation('Thân mến,' . "\n" . 'Đội ngũ ' . $brandName);

            // Người gửi tùy chỉnh từ trang Cài đặt (nếu admin có đặt)
            if ($fromAddr = Setting::get('mail_from_address')) {
                $message->from($fromAddr, Setting::get('mail_from_name') ?: $brandName);
            }

            return $message;
        });

    }
}
