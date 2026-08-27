<?php

declare(strict_types=1);

namespace Modules\Commerce\Application;

use App\Core\CommunityContext;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\DigitalProduct;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\ProductPurchase;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LogicException;

final class AdminCommerceOrderService
{
    private const TYPES = ['challenge', 'course', 'product'];

    public function __construct(private readonly CommunityContext $context) {}

    public function activate(string $type, int $orderId, User $actor): ?AdminCommerceOrderResult
    {
        $brand = $this->context->require();
        if (! $actor->isCommunityAdmin($brand->id)) {
            return null;
        }
        $this->assertType($type);
        $reference = $this->reference('ADMIN-ACTIVATE', $actor);

        return DB::transaction(function () use ($type, $orderId, $actor, $reference, $brand): AdminCommerceOrderResult {
            $result = match ($type) {
                'challenge' => $this->activateChallenge($orderId, $actor, $reference, $brand->id),
                'course' => $this->activateCourse($orderId, $reference, $brand->id),
                'product' => $this->activateProduct($orderId, $reference, $brand->id),
                default => throw new InvalidArgumentException('Unsupported order type.'),
            };
            DB::afterCommit(fn () => $result->user->notify(new GenericNotification('check', 'Đơn hàng đã được Admin kích hoạt: '.$result->label, $result->url)));

            return $result;
        });
    }

    public function grant(string $type, int $userId, int $resourceId, User $actor): ?AdminCommerceOrderResult
    {
        $brand = $this->context->require();
        if (! $actor->isCommunityAdmin($brand->id)) {
            return null;
        }
        $this->assertType($type);
        $user = User::query()->findOrFail($userId);
        $reference = $this->reference('GIFT-ADMIN', $actor);

        return DB::transaction(function () use ($type, $user, $resourceId, $actor, $reference, $brand): AdminCommerceOrderResult {
            $result = match ($type) {
                'challenge' => $this->grantChallenge($user, $resourceId, $actor, $reference, $brand->id),
                'course' => $this->grantCourse($user, $resourceId, $reference, $brand->id),
                'product' => $this->grantProduct($user, $resourceId, $reference, $brand->id),
                default => throw new InvalidArgumentException('Unsupported order type.'),
            };
            DB::afterCommit(fn () => $result->user->notify(new GenericNotification('gift', 'Bạn được tặng quyền truy cập: '.$result->label, $result->url)));

            return $result;
        });
    }

    private function activateChallenge(int $orderId, User $actor, string $reference, int $brandId): AdminCommerceOrderResult
    {
        $member = ExpeditionMember::query()->with(['user', 'expedition'])->where('brand_id', $brandId)->findOrFail($orderId);
        $member->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $actor->id,
            'payment_amount' => 0,
            'payment_ref' => $member->payment_ref ?: $reference,
            'kicked_at' => null,
        ]);
        $challenge = $member->expedition ?? throw new LogicException('Challenge order is missing its challenge.');

        return new AdminCommerceOrderResult(
            $member->user,
            $challenge->title,
            route('challenge.show', $challenge->slug),
        );
    }

    private function activateCourse(int $orderId, string $reference, int $brandId): AdminCommerceOrderResult
    {
        $enrollment = CourseEnrollment::query()->with(['user', 'course'])->where('brand_id', $brandId)->findOrFail($orderId);
        $enrollment->update([
            'status' => 'active',
            'amount_paid' => 0,
            'payment_ref' => $enrollment->payment_ref ?: $reference,
            'paid_at' => now(),
        ]);
        $course = $enrollment->course ?? throw new LogicException('Course order is missing its course.');

        return new AdminCommerceOrderResult(
            $enrollment->user,
            $course->title,
            community_route('academy.show', ['id' => $course->id]),
        );
    }

    private function activateProduct(int $orderId, string $reference, int $brandId): AdminCommerceOrderResult
    {
        $purchase = ProductPurchase::query()->with(['user', 'product'])->where('brand_id', $brandId)->findOrFail($orderId);
        $purchase->update([
            'status' => 'active',
            'amount_paid' => 0,
            'payment_ref' => $purchase->payment_ref ?: $reference,
            'paid_at' => now(),
        ]);

        return new AdminCommerceOrderResult($purchase->user, $purchase->product->title);
    }

    private function grantChallenge(User $user, int $resourceId, User $actor, string $reference, int $brandId): AdminCommerceOrderResult
    {
        $challenge = Expedition::query()->where('brand_id', $brandId)->findOrFail($resourceId);
        $member = ExpeditionMember::withoutGlobalScopes()->firstOrNew([
            'brand_id' => $brandId,
            'expedition_id' => $challenge->id,
            'user_id' => $user->id,
        ]);
        $member->fill([
            'class_at_join' => $member->class_at_join ?: $user->class,
            'joined_at' => $member->joined_at ?: now(),
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $actor->id,
            'payment_amount' => 0,
            'payment_ref' => $reference,
            'kicked_at' => null,
            'personal_starts_at' => null,
        ])->save();

        return new AdminCommerceOrderResult($user, $challenge->title, route('challenge.show', $challenge->slug));
    }

    private function grantCourse(User $user, int $resourceId, string $reference, int $brandId): AdminCommerceOrderResult
    {
        $course = Course::query()->where('brand_id', $brandId)->findOrFail($resourceId);
        $enrollment = CourseEnrollment::withoutGlobalScopes()->firstOrNew([
            'brand_id' => $brandId,
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);
        $enrollment->fill([
            'status' => 'active',
            'payment_ref' => $reference,
            'amount_paid' => 0,
            'paid_at' => now(),
            'enrolled_at' => $enrollment->enrolled_at ?: now(),
        ])->save();

        return new AdminCommerceOrderResult($user, $course->title, community_route('academy.show', ['id' => $course->id]));
    }

    private function grantProduct(User $user, int $resourceId, string $reference, int $brandId): AdminCommerceOrderResult
    {
        $product = DigitalProduct::query()->where('brand_id', $brandId)->findOrFail($resourceId);
        $purchase = ProductPurchase::withoutGlobalScopes()->firstOrNew([
            'brand_id' => $brandId,
            'user_id' => $user->id,
            'digital_product_id' => $product->id,
        ]);
        $purchase->fill([
            'status' => 'active',
            'payment_ref' => $reference,
            'amount_paid' => 0,
            'paid_at' => now(),
        ])->save();

        return new AdminCommerceOrderResult($user, $product->title);
    }

    private function reference(string $prefix, User $actor): string
    {
        return $prefix.$actor->id.'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5));
    }

    private function assertType(string $type): void
    {
        if (! in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException('Unsupported order type.');
        }
    }
}
