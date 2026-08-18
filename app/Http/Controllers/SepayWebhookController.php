<?php

namespace App\Http\Controllers;

use App\Livewire\MembershipPricing;
use App\Models\CourseEnrollment;
use App\Models\ExpeditionMember;
use App\Models\Membership;
use App\Models\ProductPurchase;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SepayWebhookController extends Controller
{
    /**
     * Handle SePay webhook for bank transfer verification.
     * Matches transfer content to pending payments (courses & memberships).
     *
     * Transfer content format: "COURSE{courseId}U{userId}", "CHAL{expeditionId}U{userId}"
     * or "MEM{weeks}WU{userId}"
     */
    public function __invoke(Request $request)
    {
        // Verify API key
        $apiKey = config('services.sepay.webhook_token');
        $authHeader = $request->header('Authorization');

        // Use hash_equals to prevent timing attacks
        if (! $apiKey || ! hash_equals('Apikey '.$apiKey, $authHeader ?? '')) {
            Log::warning('SePay webhook: invalid or missing API key');

            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Only process incoming transfers
        if ($request->input('transferType') !== 'in') {
            return response()->json(['success' => true]);
        }

        $content = strtoupper(trim($request->input('content', '')));
        $amount = (int) $request->input('transferAmount', 0);
        $webhookId = $request->input('id');
        $refCode = $request->input('referenceCode', '');

        Log::info('SePay webhook received', [
            'id' => $webhookId,
            'content' => $content,
            'amount' => $amount,
            'ref' => $refCode,
        ]);

        // Match course payment: COURSE{id}U{userId}
        if (preg_match('/COURSE(\d+)U(\d+)/', $content, $m)) {
            $this->processCoursePayment((int) $m[1], (int) $m[2], $amount, $refCode);
        }
        // Match product payment: PROD{id}U{userId}
        elseif (preg_match('/PROD(\d+)U(\d+)/', $content, $m)) {
            $this->processProductPayment((int) $m[1], (int) $m[2], $amount, $refCode);
        }
        // Match challenge payment: CHAL{expeditionId}U{userId}
        elseif (preg_match('/CHAL(\d+)U(\d+)/', $content, $m)) {
            $this->processChallengePayment((int) $m[1], (int) $m[2], $amount, $refCode);
        }
        // Match membership payment: MEM{weeks}WU{userId}
        elseif (preg_match('/MEM(\d+)WU(\d+)/', $content, $m)) {
            $this->processMembershipPayment((int) $m[1], (int) $m[2], $amount, $refCode);
        }

        return response()->json(['success' => true]);
    }

    private function processCoursePayment(int $courseId, int $userId, int $amount, string $ref): void
    {
        $enrollment = CourseEnrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('status', 'pending_payment')
            ->first();

        if (! $enrollment) {
            Log::info("SePay: no pending enrollment for course={$courseId} user={$userId}");

            return;
        }

        // Check if already processed (idempotency)
        if ($enrollment->payment_ref === $ref && $enrollment->status === 'active') {
            return;
        }

        $course = $enrollment->course;
        if ($amount < $course->price) {
            Log::warning("SePay: underpayment course={$courseId} user={$userId}", [
                'expected' => $course->price,
                'received' => $amount,
            ]);

            return;
        }

        $enrollment->update([
            'status' => 'active',
            'payment_ref' => $ref,
            'amount_paid' => $amount,
            'paid_at' => now(),
        ]);

        // Notify user
        $user = $enrollment->user;
        $user->notify(new GenericNotification(
            '🎓',
            'Thanh toán thành công! Khóa học "'.$course->title.'" đã được mở.',
            route('academy.show', $course->id)
        ));

        Log::info('SePay: course enrollment activated', [
            'course' => $courseId,
            'user' => $userId,
            'amount' => $amount,
        ]);
    }

    private function processProductPayment(int $productId, int $userId, int $amount, string $ref): void
    {
        $purchase = ProductPurchase::where('user_id', $userId)
            ->where('digital_product_id', $productId)
            ->where('status', 'pending_payment')
            ->first();

        if (! $purchase) {
            Log::info("SePay: no pending purchase for product={$productId} user={$userId}");

            return;
        }

        if ($purchase->payment_ref === $ref && $purchase->status === 'active') {
            return;
        }

        $product = $purchase->product;
        if ($amount < $product->price) {
            Log::warning("SePay: underpayment product={$productId} user={$userId}", [
                'expected' => $product->price,
                'received' => $amount,
            ]);

            return;
        }

        $purchase->update([
            'status' => 'active',
            'payment_ref' => $ref,
            'amount_paid' => $amount,
            'paid_at' => now(),
        ]);

        $user = $purchase->user;
        $user->notify(new GenericNotification(
            '📦',
            'Thanh toán thành công! Sản phẩm "'.$product->title.'" đã được mở.',
            route('marketplace')
        ));

        Log::info('SePay: product purchase activated', [
            'product' => $productId,
            'user' => $userId,
            'amount' => $amount,
        ]);
    }

    private function processChallengePayment(int $expeditionId, int $userId, int $amount, string $ref): void
    {
        // Chỉ khớp bản ghi đang chờ tiền — webhook gửi lại lần 2 sẽ không tìm thấy nữa (idempotent).
        $member = ExpeditionMember::where('expedition_id', $expeditionId)
            ->where('user_id', $userId)
            ->where('status', 'pending_payment')
            ->first();

        if (! $member) {
            Log::info("SePay: no pending_payment member for challenge={$expeditionId} user={$userId}");

            return;
        }

        $expedition = $member->expedition;
        if ($amount < (int) $expedition->price) {
            Log::warning("SePay: underpayment challenge={$expeditionId} user={$userId}", [
                'expected' => (int) $expedition->price,
                'received' => $amount,
            ]);

            return;
        }

        // Đã trả tiền = được vào. personal_starts_at vẫn null: member tự bấm "Bắt đầu".
        // approved_by null = hệ thống tự duyệt, không phải admin nào.
        $member->update([
            'status' => 'approved',
            'approved_at' => now(),
            'payment_ref' => $ref,
            'payment_amount' => $amount,
        ]);

        $member->user?->notify(new GenericNotification(
            '✅',
            'Thanh toán thành công! Bạn đã vào "'.$expedition->title.'". Bấm "Bắt đầu" khi sẵn sàng.',
            route('challenge.show', $expedition->slug)
        ));

        Log::info('SePay: challenge member approved', [
            'challenge' => $expeditionId,
            'user' => $userId,
            'amount' => $amount,
        ]);
    }

    private function processMembershipPayment(int $weeks, int $userId, int $amount, string $ref): void
    {
        $user = User::find($userId);
        if (! $user) {
            Log::info("SePay: user not found for membership user={$userId}");

            return;
        }

        // Idempotency: check if this ref already processed
        if (Membership::where('user_id', $userId)->where('payment_ref', $ref)->exists()) {
            return;
        }

        // Validate plan and amount
        $plans = MembershipPricing::PLANS;
        if (! isset($plans[$weeks])) {
            Log::warning("SePay: invalid membership plan weeks={$weeks}");

            return;
        }
        $expectedAmount = $plans[$weeks]['weeks'] * $plans[$weeks]['price_per_week'];
        if ($amount < $expectedAmount) {
            Log::warning("SePay: underpayment membership weeks={$weeks} user={$userId}", [
                'expected' => $expectedAmount,
                'received' => $amount,
            ]);

            return;
        }

        // Extend membership: if active, extend from current expiry; otherwise from now
        $currentMembership = $user->membership;
        $startsAt = ($currentMembership && $currentMembership->isActive())
            ? $currentMembership->expires_at
            : now();

        Membership::create([
            'user_id' => $userId,
            'plan' => $weeks.'w',
            'status' => 'active',
            'starts_at' => $startsAt,
            'expires_at' => $startsAt->copy()->addWeeks($weeks),
            'paid_amount' => $amount,
            'payment_ref' => $ref,
        ]);

        $user->notify(new GenericNotification(
            '✅',
            "Membership {$weeks} tuần đã được kích hoạt! Hết hạn: ".$startsAt->copy()->addWeeks($weeks)->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y'),
            route('feed')
        ));

        Log::info('SePay: membership activated', [
            'user' => $userId,
            'weeks' => $weeks,
            'amount' => $amount,
            'expires' => $startsAt->copy()->addWeeks($weeks)->toDateString(),
        ]);
    }
}
