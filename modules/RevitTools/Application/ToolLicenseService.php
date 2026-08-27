<?php

declare(strict_types=1);

namespace Modules\RevitTools\Application;

use App\Core\Audit\AuditLogger;
use App\Models\Brand;
use App\Models\DigitalProduct;
use App\Models\ProductPurchase;
use App\Models\ToolDeviceAuthorization;
use App\Models\ToolInstallation;
use App\Models\ToolSecurityEvent;
use App\Models\ToolSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ToolLicenseService
{
    public const PLATFORM = 'revit';

    private const SESSION_DAYS = 7;

    private const AUTHORIZATION_MINUTES = 10;

    public function __construct(private readonly AuditLogger $audit) {}

    public function revitBrand(): Brand
    {
        return Brand::query()->where('slug', 'dscons')->firstOrFail();
    }

    public function findAuthorizationByCode(Brand $brand, string $code): ?ToolDeviceAuthorization
    {
        return ToolDeviceAuthorization::withoutGlobalScopes()
            ->where('brand_id', $brand->id)
            ->where('code_hash', hash('sha256', $code))
            ->first();
    }

    public function findAuthorizationByBrowserCode(string $code): ?ToolDeviceAuthorization
    {
        return ToolDeviceAuthorization::withoutGlobalScopes()
            ->where('brand_id', $this->revitBrand()->id)
            ->where('browser_code_hash', hash('sha256', $code))
            ->first();
    }

    public function findPublishedTool(ToolInstallation $installation, string $toolKey): ?DigitalProduct
    {
        return DigitalProduct::withoutGlobalScopes()
            ->where('brand_id', $installation->brand_id)
            ->where('product_kind', 'revit_tool')
            ->where('tool_key', $toolKey)
            ->where('is_published', true)
            ->first();
    }

    /**
     * @param  array<string, string|null>  $input
     * @return array{code:string,browserCode:string,authorization:ToolDeviceAuthorization}
     */
    public function startDeviceAuthorization(Brand $brand, array $input): array
    {
        $code = Str::random(64);
        $browserCode = Str::random(64);
        $authorization = ToolDeviceAuthorization::withoutGlobalScopes()->create([
            'brand_id' => $brand->id,
            'platform' => self::PLATFORM,
            'code_hash' => hash('sha256', $code),
            'browser_code_hash' => hash('sha256', $browserCode),
            'installation_id_hash' => $this->hashIdentifier((string) $input['installation_id']),
            'device_fingerprint_hash' => $this->hashIdentifier((string) $input['device_fingerprint']),
            'device_label' => $this->safeDeviceLabel($input['device_label'] ?? null),
            'revit_version' => $this->safeVersion($input['revit_version'] ?? null),
            'client_version' => $this->safeVersion($input['client_version'] ?? null),
            'status' => 'pending',
            'expires_at' => now()->addMinutes(self::AUTHORIZATION_MINUTES),
        ]);

        return compact('code', 'browserCode', 'authorization');
    }

    /** @return array{installation:ToolInstallation, token:string, expires_at:string}|array{blocked:bool,message:string} */
    public function approveDeviceAuthorization(ToolDeviceAuthorization $authorization, User $user): array
    {
        if ($authorization->status !== 'pending' || $authorization->expires_at->isPast()) {
            return ['blocked' => true, 'message' => 'Mã kích hoạt đã hết hạn hoặc đã được sử dụng. Hãy quay lại Revit để tạo mã mới.'];
        }

        return DB::transaction(function () use ($authorization, $user): array {
            $existing = ToolInstallation::withoutGlobalScopes()
                ->where('brand_id', $authorization->brand_id)
                ->where('user_id', $user->id)
                ->where('platform', self::PLATFORM)
                ->lockForUpdate()
                ->first();

            if ($existing && $existing->status === 'blocked') {
                $this->recordSecurityEvent($authorization->brand_id, $user->id, $existing->id, 'activation_denied_blocked', 'critical');

                return ['blocked' => true, 'message' => 'License Revit đang tạm khóa. Vui lòng liên hệ quản trị viên DSCons.'];
            }

            if ($existing && $existing->blocked_until?->isFuture()
                && ! hash_equals($existing->device_fingerprint_hash, $authorization->device_fingerprint_hash)) {
                $this->recordSecurityEvent($authorization->brand_id, $user->id, $existing->id, 'activation_denied_temporarily', 'warning');

                return ['blocked' => true, 'message' => 'Kích hoạt thiết bị mới đang tạm dừng đến '.$existing->blocked_until->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y').'. Hãy đăng xuất thiết bị cũ hoặc liên hệ admin DSCons.'];
            }

            if ($existing && $existing->status === 'active' && ! hash_equals($existing->device_fingerprint_hash, $authorization->device_fingerprint_hash)) {
                $count = ToolSecurityEvent::withoutGlobalScopes()
                    ->where('brand_id', $authorization->brand_id)
                    ->where('user_id', $user->id)
                    ->where('event_type', 'activation_conflict')
                    ->count() + 1;
                $severity = $count >= 3 ? 'critical' : ($count === 2 ? 'warning' : 'notice');
                $this->recordSecurityEvent($authorization->brand_id, $user->id, $existing->id, 'activation_conflict', $severity, ['attempt' => $count]);

                if ($count >= 3) {
                    $existing->update(['status' => 'blocked', 'blocked_until' => null]);
                    $existing->sessions()->update(['revoked_at' => now()]);

                    return ['blocked' => true, 'message' => 'Đã phát hiện lần kích hoạt thiết bị thứ 3. License tạm khóa để DSCons kiểm tra.'];
                }

                if ($count === 2) {
                    $existing->update(['blocked_until' => now()->addDay()]);
                }

                $message = $count === 2
                    ? 'Bạn đã thử kích hoạt thiết bị thứ 2 lần thứ 2. Kích hoạt mới tạm dừng 24 giờ; hãy đăng xuất thiết bị cũ hoặc liên hệ admin.'
                    : 'Tài khoản này đã có một thiết bị Revit đang hoạt động. Hãy đăng xuất thiết bị cũ trong trang tài khoản trước khi đổi máy.';

                return ['blocked' => true, 'message' => $message];
            }

            $installation = $existing ?: new ToolInstallation([
                'brand_id' => $authorization->brand_id,
                'user_id' => $user->id,
                'platform' => self::PLATFORM,
            ]);
            $installation->fill([
                'installation_id_hash' => $authorization->installation_id_hash,
                'device_fingerprint_hash' => $authorization->device_fingerprint_hash,
                'device_label' => $authorization->device_label,
                'status' => 'active',
                'blocked_until' => null,
                'first_seen_at' => $installation->first_seen_at ?: now(),
                'last_seen_at' => now(),
                'last_revit_version' => $authorization->revit_version,
                'last_client_version' => $authorization->client_version,
                'revoked_at' => null,
            ])->save();

            $session = $this->issueSession($installation);
            $authorization->update([
                'status' => 'approved',
                'approved_by_user_id' => $user->id,
                'tool_session_id' => $session['session']->id,
                'approved_at' => now(),
            ]);
            $this->recordSecurityEvent($authorization->brand_id, $user->id, $installation->id, 'activation_approved', 'info');

            return ['installation' => $installation, 'token' => $session['token'], 'expires_at' => $session['session']->expires_at->toIso8601String()];
        });
    }

    /** @return array{token:string, session:ToolSession} */
    public function issueSession(ToolInstallation $installation): array
    {
        $raw = Str::random(80);
        $session = ToolSession::create([
            'tool_installation_id' => $installation->id,
            'token_hash' => hash('sha256', $raw),
            'expires_at' => now()->addDays(self::SESSION_DAYS),
            'last_seen_at' => now(),
        ]);

        return ['token' => $raw, 'session' => $session];
    }

    public function sessionFromToken(?string $token): ?ToolSession
    {
        if (! is_string($token) || strlen($token) < 32) {
            return null;
        }

        return ToolSession::with(['installation.user'])
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    /** @return array<int, array<string, mixed>> */
    public function getEntitlements(ToolInstallation $installation): array
    {
        return ProductPurchase::withoutGlobalScopes()
            ->where('brand_id', $installation->brand_id)
            ->where('user_id', $installation->user_id)
            ->where('status', 'active')
            ->whereHas('product', fn ($query) => $query->withoutGlobalScopes()->where('product_kind', 'revit_tool')->where('is_published', true))
            ->with(['product' => fn ($query) => $query->withoutGlobalScopes()])
            ->get()
            ->map(function (ProductPurchase $purchase): array {
                $product = $purchase->product;

                return [
                    'tool_key' => $product->tool_key,
                    'title' => $product->title,
                    'manifest_version' => $product->tool_manifest_version,
                    'supported_revit_versions' => $product->supported_revit_versions ?: [],
                    'active' => true,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, string|null>  $input
     * @return array{session:ToolSession, replacement_token:?string, entitlements:array<int,array<string,mixed>>}|null
     */
    public function heartbeat(ToolSession $session, array $input): ?array
    {
        $installation = $session->installation;
        if ($installation->status !== 'active') {
            return null;
        }

        $installation->update([
            'last_seen_at' => now(),
            'last_revit_version' => $this->safeVersion($input['revit_version'] ?? null) ?: $installation->last_revit_version,
            'last_client_version' => $this->safeVersion($input['client_version'] ?? null) ?: $installation->last_client_version,
        ]);
        $session->update(['last_seen_at' => now()]);

        $replacementToken = null;
        if ($session->expires_at->lessThanOrEqualTo(now()->addDays(2))) {
            $session->update(['revoked_at' => now()]);
            $issued = $this->issueSession($installation);
            $session = $issued['session'];
            $replacementToken = $issued['token'];
        }

        return ['session' => $session, 'replacement_token' => $replacementToken, 'entitlements' => $this->getEntitlements($installation)];
    }

    public function revokeInstallation(ToolInstallation $installation, string $event = 'installation_revoked'): void
    {
        DB::transaction(function () use ($installation, $event): void {
            $installation->update(['status' => 'revoked', 'revoked_at' => now()]);
            $installation->sessions()->whereNull('revoked_at')->update(['revoked_at' => now()]);
            $this->recordSecurityEvent($installation->brand_id, $installation->user_id, $installation->id, $event, 'notice');
        });
    }

    public function resetInstallation(ToolInstallation $installation, ?User $actor = null): void
    {
        DB::transaction(function () use ($installation, $actor): void {
            $installation->sessions()->whereNull('revoked_at')->update(['revoked_at' => now()]);
            $installation->delete();
            $this->recordSecurityEvent($installation->brand_id, $installation->user_id, null, 'installation_reset', 'notice', ['actor_id' => $actor?->id]);
        });
    }

    /** Unlock a suspended license by removing its device binding. */
    public function unlockInstallation(ToolInstallation $installation, ?User $actor = null): void
    {
        $this->resetInstallation($installation, $actor);
    }

    /** @param array<string, scalar|null> $metadata */
    public function recordSecurityEvent(int $brandId, ?int $userId, ?int $installationId, string $event, string $severity, array $metadata = []): void
    {
        $securityEvent = ToolSecurityEvent::withoutGlobalScopes()->create([
            'brand_id' => $brandId,
            'user_id' => $userId,
            'tool_installation_id' => $installationId,
            'event_type' => $event,
            'severity' => $severity,
            'metadata' => $metadata,
        ]);
        DB::afterCommit(function () use ($securityEvent, $userId, $brandId, $event): void {
            $actor = $userId === null ? null : User::query()->find($userId);
            $this->audit->record('revit_tools', $event, $actor, $securityEvent, $brandId);
        });
    }

    private function hashIdentifier(string $value): string
    {
        return hash('sha256', trim($value));
    }

    private function safeDeviceLabel(?string $value): ?string
    {
        return filled($value) ? Str::limit(strip_tags($value), 120, '') : null;
    }

    private function safeVersion(?string $value): ?string
    {
        return filled($value) ? Str::limit(preg_replace('/[^A-Za-z0-9. _-]/', '', $value) ?? '', 40, '') : null;
    }
}
