<?php

namespace App\Livewire;

use App\Models\Setting;
use App\Support\CommunityBrandSettings;
use App\Models\EngineerProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Component;

class AdminSettings extends Component
{
    #[Rule('required|in:log,resend,brevo')]
    public string $mailDriver = 'log';

    // Write-only: để trống = giữ key đã lưu. Không bao giờ trả key về frontend.
    #[Rule('nullable|max:200')]
    public string $resendApiKey = '';

    public bool $resendApiKeySet = false;

    #[Rule('nullable|max:200')]
    public string $brevoApiKey = '';

    public bool $brevoApiKeySet = false;

    #[Rule('nullable|max:100')]
    public string $mailFromName = '';

    #[Rule('required|email')]
    public string $mailFromAddress = '';

    public string $testEmail = '';

    // ─── SePay (thanh toán chuyển khoản) ─────────────────────
    #[Rule('nullable|max:50')]
    public string $sepayBankAccount = '';

    #[Rule('nullable|max:50')]
    public string $sepayBankName = '';

    // Write-only: để trống = giữ token đã lưu. Không bao giờ trả token về frontend.
    #[Rule('nullable|max:200')]
    public string $sepayWebhookToken = '';

    public bool $sepayWebhookTokenSet = false;

    // ─── Webhook tạo thành viên (server-to-server) ───────────
    // Secret hiển thị dạng plaintext cho admin copy đi cấu hình bên thứ 3.
    public string $webhookSecret = '';

    #[Rule('required|string|max:80')]
    public string $membershipLabel = '';

    /** @var array<string, string> */
    public array $stageLabels = [];

    /** @var array<string, string> */
    public array $badgeColors = [];

    #[Rule('required|integer|min:28|max:56')]
    public int $memberAvatarSize = 30;
    public bool $hasCv = false;
    public bool $hasRecruitment = false;

    public array $levelBands = [
        'newcomer' => 'Level 1–10',
        'practitioner' => 'Level 11–30',
        'core' => 'Level 31–60',
        'expert' => 'Level 61–100',
        'mentor' => 'Level 101+',
    ];

    public function mount(): void
    {
        $driver = Setting::get('mail_driver', config('mail.default'));
        $this->mailDriver = in_array($driver, ['log', 'resend'], true) ? $driver : 'log';
        $this->resendApiKeySet = filled(Setting::get('resend_api_key')) || filled(config('services.resend.key'));
        $this->brevoApiKeySet  = filled(Setting::get('brevo_api_key')) || filled(config('services.brevo.key'));
        $this->mailFromName       = Setting::get('mail_from_name', config('mail.from.name')) ?? '';
        $this->mailFromAddress    = Setting::get('mail_from_address', config('mail.from.address')) ?? '';
        $this->webhookSecret      = Setting::get('register_webhook_secret', '') ?? '';

        $this->sepayBankAccount     = Setting::get('sepay_bank_account', config('services.sepay.bank_account')) ?? '';
        $this->sepayBankName        = Setting::get('sepay_bank_name', config('services.sepay.bank_name')) ?? '';
        $this->sepayWebhookTokenSet = filled(Setting::get('sepay_webhook_token')) || filled(config('services.sepay.webhook_token'));

        $this->membershipLabel = CommunityBrandSettings::membershipLabel(brand());
        $this->stageLabels = CommunityBrandSettings::stageLabels(brand());
        $this->badgeColors = CommunityBrandSettings::badgeColors(brand());
        $this->memberAvatarSize = CommunityBrandSettings::memberAvatarSize(brand());
        $this->hasCv = (bool) brand()->has_cv;
        $this->hasRecruitment = (bool) brand()->has_recruitment;
    }

    public function saveCommunityBranding(): void
    {
        if (!Auth::user()?->isBrandAdmin(brand()->id)) return;

        $this->validate([
            'membershipLabel' => 'required|string|max:80',
            'stageLabels' => 'required|array',
            'stageLabels.*' => 'required|string|max:80',
            'badgeColors' => 'required|array',
            'badgeColors.*' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'memberAvatarSize' => 'required|integer|min:28|max:56',
        ]);

        CommunityBrandSettings::save(
            brand(),
            $this->membershipLabel,
            $this->stageLabels,
            $this->badgeColors,
            $this->memberAvatarSize,
        );

        brand()->update([
            'has_cv' => $this->hasCv,
            'has_recruitment' => $this->hasRecruitment,
        ]);
        if (! $this->hasCv || ! $this->hasRecruitment) {
            EngineerProfile::query()->update(['is_searchable' => false]);
        }

        $this->dispatch('toast', message: 'Đã lưu cấu hình cộng đồng, level và membership.', type: 'success');
    }

    public function save(): void
    {
        if (!Auth::user()?->isBrandAdmin(brand()->id)) return;
        $this->validate();

        // Xác minh email đã được gỡ bỏ — luôn tắt để thành viên vào thẳng.
        Setting::set('email_verification_required', '0');
        Setting::set('mail_driver', $this->mailDriver);
        Setting::set('mail_from_name', $this->mailFromName);
        Setting::set('mail_from_address', $this->mailFromAddress);

        // Chỉ ghi đè key khi admin nhập mới — để trống thì giữ key cũ.
        if (filled($this->resendApiKey)) {
            Setting::set('resend_api_key', trim($this->resendApiKey));
            $this->resendApiKey = '';
            $this->resendApiKeySet = true;
        }
        if (filled($this->brevoApiKey)) {
            Setting::set('brevo_api_key', trim($this->brevoApiKey));
            $this->brevoApiKey = '';
            $this->brevoApiKeySet = true;
        }

        // Áp dụng ngay trong request này để phần "Gửi email thử" dùng đúng cấu hình mới.
        config(['mail.default' => $this->mailDriver]);
        if ($key = Setting::get('resend_api_key')) config(['services.resend.key' => $key]);
        if ($key = Setting::get('brevo_api_key'))  config(['services.brevo.key' => $key]);

        $this->dispatch('toast', message: 'Đã lưu cấu hình email.', type: 'success');
    }

    public function sendTest(): void
    {
        if (!Auth::user()?->isBrandAdmin(brand()->id)) return;

        $this->validate(
            ['testEmail' => 'required|email'],
            ['testEmail.required' => 'Nhập một email để gửi thử.', 'testEmail.email' => 'Email không hợp lệ.']
        );

        $key = 'send-test-mail|' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('testEmail', 'Gửi thử quá nhiều lần. Đợi ' . RateLimiter::availableIn($key) . ' giây.');
            return;
        }
        RateLimiter::hit($key, 60);

        $to        = $this->testEmail;
        $brandName = brand()->name;
        $fromAddr  = $this->mailFromAddress ?: config('mail.from.address');
        $fromName  = $this->mailFromName ?: $brandName;
        $body      = "Đây là email thử nghiệm từ {$brandName}.\n\n"
                   . "Nếu bạn nhận được email này, cấu hình gửi mail đang hoạt động bình thường.\n\n"
                   . 'Mailer đang dùng: ' . config('mail.default');

        try {
            Mail::raw($body, function ($message) use ($to, $fromAddr, $fromName, $brandName) {
                $message->to($to)
                    ->from($fromAddr, $fromName)
                    ->subject("Email thử nghiệm — {$brandName}");
            });
            $this->dispatch('toast', message: 'Đã gửi email thử tới ' . $to . '.', type: 'success');
        } catch (\Throwable $e) {
            $this->addError('testEmail', 'Gửi thất bại: ' . $e->getMessage());
        }
    }

    // ─── SePay ────────────────────────────────────────────────
    public function saveSepay(): void
    {
        if (!Auth::user()?->isBrandAdmin(brand()->id)) return;

        $this->validate([
            'sepayBankAccount'  => 'nullable|max:50',
            'sepayBankName'     => 'nullable|max:50',
            'sepayWebhookToken' => 'nullable|max:200',
        ]);

        Setting::set('sepay_bank_account', trim($this->sepayBankAccount));
        Setting::set('sepay_bank_name', trim($this->sepayBankName));

        // Chỉ ghi đè token khi admin nhập mới — để trống thì giữ token cũ.
        if (filled($this->sepayWebhookToken)) {
            Setting::set('sepay_webhook_token', trim($this->sepayWebhookToken));
            $this->sepayWebhookToken = '';
            $this->sepayWebhookTokenSet = true;
        }

        $this->dispatch('toast', message: 'Đã lưu cấu hình SePay.', type: 'success');
    }

    // ─── Webhook ──────────────────────────────────────────────
    public function generateWebhookSecret(): void
    {
        if (!Auth::user()?->isBrandAdmin(brand()->id)) return;
        // Sinh tại chỗ để admin xem trước; phải bấm "Lưu webhook" mới có hiệu lực.
        $this->webhookSecret = 'whk_' . Str::random(48);
    }

    public function saveWebhook(): void
    {
        if (!Auth::user()?->isBrandAdmin(brand()->id)) return;

        $this->validate(
            ['webhookSecret' => 'required|string|min:16|max:200'],
            [
                'webhookSecret.required' => 'Bấm "Tạo key mới" hoặc dán một secret trước khi lưu.',
                'webhookSecret.min'      => 'Secret quá ngắn (tối thiểu 16 ký tự).',
            ]
        );

        Setting::set('register_webhook_secret', trim($this->webhookSecret));
        $this->dispatch('toast', message: 'Đã lưu secret webhook. Bên thứ 3 dùng key này để bắn webhook.', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin-settings', [
            'webhookUrl'      => url('/webhook/register'),
            'sepayWebhookUrl' => url('/webhook/sepay'),
            'levelBands'      => $this->levelBands,
        ])->layout('layouts.app');
    }
}
