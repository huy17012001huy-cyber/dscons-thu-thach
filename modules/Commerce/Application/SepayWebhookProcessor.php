<?php

declare(strict_types=1);

namespace Modules\Commerce\Application;

use App\Core\Audit\AuditLogger;
use App\Models\CommerceWebhookEvent;
use App\Models\CourseEnrollment;
use App\Models\DigitalProduct;
use App\Models\ExpeditionMember;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\ProductPurchase;
use App\Models\RecruiterOrder;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Support\Facades\DB;
use Modules\Commerce\Domain\LegacyMembershipPlans;
use Modules\Commerce\Domain\PaymentReference;
use Modules\Commerce\Domain\PaymentReferenceParser;

final class SepayWebhookProcessor
{
    public function __construct(
        private readonly PaymentReferenceParser $references,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $payload */
    public function process(array $payload): void
    {
        if (($payload['transferType'] ?? null) !== 'in') {
            return;
        }

        $reference = $this->references->parse((string) ($payload['content'] ?? ''));
        $externalEventId = $this->externalEventId($payload);
        $amount = (int) ($payload['transferAmount'] ?? 0);
        $paymentReference = trim((string) ($payload['referenceCode'] ?? ''));

        DB::transaction(function () use ($payload, $reference, $externalEventId, $amount, $paymentReference): void {
            $created = DB::table('commerce_webhook_events')->insertOrIgnore([
                'provider' => 'sepay',
                'external_event_id' => $externalEventId,
                'payload_hash' => $this->payloadHash($payload),
                'payment_reference' => $paymentReference ?: null,
                'status' => 'processing',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($created !== 1) {
                return;
            }

            $event = CommerceWebhookEvent::query()
                ->where('provider', 'sepay')
                ->where('external_event_id', $externalEventId)
                ->lockForUpdate()
                ->firstOrFail();

            $processed = $reference ? $this->processPayment($reference, $amount, $paymentReference) : false;
            $event->update([
                'status' => $processed ? 'processed' : 'ignored',
                'processed_at' => now(),
            ]);

            if ($processed) {
                DB::afterCommit(fn () => $this->audit->record(
                    'commerce',
                    'sepay_payment_processed',
                    null,
                    $event,
                    null,
                    ['payment_type' => $reference->type],
                ));
            }
        }, 3);
    }

    private function processPayment(PaymentReference $reference, int $amount, string $paymentReference): bool
    {
        return match ($reference->type) {
            'course' => $this->activateCourse($reference, $amount, $paymentReference),
            'product' => $this->activateProduct($reference, $amount, $paymentReference),
            'challenge' => $this->activateChallenge($reference, $amount, $paymentReference),
            'membership' => $this->activateLegacyMembership($reference, $amount, $paymentReference),
            'community_membership' => $this->activateCommunityMembership($reference, $amount, $paymentReference),
            'recruiter_plan' => $this->activateRecruiterPlan($reference, $amount, $paymentReference),
            default => false,
        };
    }

    private function activateCourse(PaymentReference $reference, int $amount, string $paymentReference): bool
    {
        $enrollment = CourseEnrollment::query()
            ->with(['course', 'user'])
            ->where('user_id', $reference->attributes['user_id'])
            ->where('course_id', $reference->attributes['course_id'])
            ->where('status', 'pending_payment')
            ->lockForUpdate()
            ->first();

        if (! $enrollment || ! $enrollment->course || $amount < (int) $enrollment->course->price) {
            return false;
        }

        $enrollment->update([
            'status' => 'active',
            'payment_ref' => $paymentReference,
            'amount_paid' => $amount,
            'paid_at' => now(),
        ]);

        $this->notifyAfterCommit($enrollment->user, 'Thanh toán thành công! Khóa học "'.$enrollment->course->title.'" đã được mở.', route('academy.show', $enrollment->course->id));

        return true;
    }

    private function activateProduct(PaymentReference $reference, int $amount, string $paymentReference): bool
    {
        $purchase = ProductPurchase::query()
            ->with(['product', 'user'])
            ->where('user_id', $reference->attributes['user_id'])
            ->where('digital_product_id', $reference->attributes['product_id'])
            ->where('status', 'pending_payment')
            ->lockForUpdate()
            ->first();

        $product = $purchase?->product;
        if (! $purchase || ! $product instanceof DigitalProduct || $amount < (int) $product->price) {
            return false;
        }

        $purchase->update([
            'status' => 'active',
            'payment_ref' => $paymentReference,
            'amount_paid' => $amount,
            'paid_at' => now(),
        ]);

        $this->notifyAfterCommit($purchase->user, 'Thanh toán thành công! Sản phẩm "'.$purchase->product->title.'" đã được mở.', route('marketplace'));

        return true;
    }

    private function activateChallenge(PaymentReference $reference, int $amount, string $paymentReference): bool
    {
        $member = ExpeditionMember::query()
            ->with(['expedition', 'user'])
            ->where('expedition_id', $reference->attributes['challenge_id'])
            ->where('user_id', $reference->attributes['user_id'])
            ->where('status', 'pending_payment')
            ->lockForUpdate()
            ->first();

        if (! $member || ! $member->expedition || $amount < (int) $member->expedition->price) {
            return false;
        }

        $member->update([
            'status' => 'approved',
            'approved_at' => now(),
            'payment_ref' => $paymentReference,
            'payment_amount' => $amount,
        ]);

        $this->notifyAfterCommit($member->user, 'Thanh toán thành công! Bạn đã vào "'.$member->expedition->title.'". Bấm "Bắt đầu" khi sẵn sàng.', route('challenge.show', $member->expedition->slug));

        return true;
    }

    private function activateLegacyMembership(PaymentReference $reference, int $amount, string $paymentReference): bool
    {
        $weeks = $reference->attributes['weeks'];
        $user = User::query()->lockForUpdate()->find($reference->attributes['user_id']);
        $plan = LegacyMembershipPlans::PLANS[$weeks] ?? null;

        if (! $user || ! $plan || $amount < $plan['weeks'] * $plan['price_per_week']) {
            return false;
        }

        if (Membership::query()->where('user_id', $user->id)->where('payment_ref', $paymentReference)->exists()) {
            return false;
        }

        $current = Membership::query()->where('user_id', $user->id)->latest()->first();
        $startsAt = $current?->isActive() && $current->expires_at !== null
            ? $current->expires_at
            : now();
        $expiresAt = $startsAt->copy()->addWeeks($weeks);

        Membership::create([
            'user_id' => $user->id,
            'plan' => $weeks.'w',
            'status' => 'active',
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'paid_amount' => $amount,
            'payment_ref' => $paymentReference,
        ]);

        $this->notifyAfterCommit($user, "Membership {$weeks} tuần đã được kích hoạt! Hết hạn: ".$expiresAt->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y'), route('feed'));

        return true;
    }

    private function activateCommunityMembership(PaymentReference $reference, int $amount, string $paymentReference): bool
    {
        $brandId = $reference->attributes['brand_id'];
        $plan = MembershipPlan::withoutGlobalScopes()
            ->with('brand')
            ->whereKey($reference->attributes['plan_id'])
            ->where('brand_id', $brandId)
            ->where('tier', 'premium')
            ->where('status', 'published')
            ->lockForUpdate()
            ->first();
        $user = User::query()->lockForUpdate()->find($reference->attributes['user_id']);

        if (! $plan || ! $user || (int) $plan->price <= 0 || $amount < (int) $plan->price) {
            return false;
        }

        if (Membership::withoutGlobalScopes()
            ->where('brand_id', $brandId)
            ->where('user_id', $user->id)
            ->where('payment_ref', $paymentReference)
            ->exists()) {
            return false;
        }

        $current = Membership::withoutGlobalScopes()
            ->where('brand_id', $brandId)
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'trial'])
            ->latest()
            ->lockForUpdate()
            ->first();
        $startsAt = $current?->expires_at?->isFuture() ? $current->expires_at : now();
        $expiresAt = $plan->duration_days ? $startsAt->copy()->addDays($plan->duration_days) : null;

        Membership::withoutGlobalScopes()->create([
            'brand_id' => $brandId,
            'user_id' => $user->id,
            'plan' => 'community-'.$plan->id,
            'tier' => 'premium',
            'status' => 'active',
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'paid_amount' => $amount,
            'payment_ref' => $paymentReference,
        ]);
        DB::table('brand_user')->updateOrInsert(
            ['brand_id' => $brandId, 'user_id' => $user->id],
            ['role' => 'member', 'updated_at' => now(), 'created_at' => now()],
        );

        $this->notifyAfterCommit($user, 'Membership Premium đã được kích hoạt.', url('/c/'.$plan->brand?->slug.'/feed'));

        return true;
    }

    private function activateRecruiterPlan(PaymentReference $reference, int $amount, string $paymentReference): bool
    {
        $planId = $reference->attributes['plan_id'];
        $userId = $reference->attributes['user_id'];
        $order = RecruiterOrder::withoutGlobalScopes()
            ->with(['plan' => fn ($query) => $query->withoutGlobalScopes()])
            ->where('recruiter_id', $userId)
            ->where('plan_id', $planId)
            ->where('status', 'pending_payment')
            ->where('payment_ref', 'RECPLAN'.$planId.'U'.$userId)
            ->lockForUpdate()
            ->first();

        if (! $order || ! $order->plan || $amount < (int) $order->amount) {
            return false;
        }

        $order->update([
            'status' => 'active',
            'amount_paid' => $amount,
            'paid_at' => now(),
            'payment_ref' => $paymentReference ?: $order->payment_ref,
        ]);
        $order->entitlement()->firstOrCreate([], [
            'brand_id' => $order->brand_id,
            'recruiter_id' => $order->recruiter_id,
            'credits_total' => $order->plan->contact_credits,
            'starts_at' => now(),
            'expires_at' => $order->plan->duration_days ? now()->addDays($order->plan->duration_days) : null,
        ]);

        $this->notifyAfterCommit(User::find($order->recruiter_id), 'Gói tuyển dụng đã được kích hoạt.', route('recruiter.dashboard'));

        return true;
    }

    private function notifyAfterCommit(?User $user, string $message, string $url): void
    {
        if (! $user) {
            return;
        }

        DB::afterCommit(fn () => $user->notify(new GenericNotification('check', $message, $url)));
    }

    /** @param array<string, mixed> $payload */
    private function externalEventId(array $payload): string
    {
        $id = trim((string) ($payload['id'] ?? ''));

        return $id !== '' ? $id : 'fallback:'.$this->payloadHash($payload);
    }

    /** @param array<string, mixed> $payload */
    private function payloadHash(array $payload): string
    {
        return hash('sha256', json_encode([
            'id' => $payload['id'] ?? null,
            'referenceCode' => $payload['referenceCode'] ?? null,
            'transferType' => $payload['transferType'] ?? null,
            'content' => $payload['content'] ?? null,
            'transferAmount' => $payload['transferAmount'] ?? null,
        ], JSON_THROW_ON_ERROR));
    }
}
