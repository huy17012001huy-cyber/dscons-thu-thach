<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\DigitalProduct;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\Membership;
use App\Models\ProductPurchase;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

class OrdersPage extends Component
{
    #[Url]
    public string $tab = 'overview';

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['overview', 'purchased', 'unowned', 'pending', 'expired'], true)) {
            $this->tab = $tab;
        }
    }

    public function render()
    {
        $user = Auth::user();
        $userId = $user->id;
        $hasPremium = $user->hasPremiumMembership();

        $membership = Membership::where('user_id', $userId)->latest()->first();
        $membershipHistory = Membership::where('user_id', $userId)->latest()->get();
        $orders = collect();

        foreach ($membershipHistory as $record) {
            $status = $record->isActive() ? 'active' : ($record->status === 'expired' ? 'expired' : $record->status);
            $orders->push((object) [
                'kind' => 'membership', 'kind_label' => 'Membership', 'title' => ucfirst($record->tier ?: 'Free').' · '.brand()->name,
                'amount' => (int) $record->paid_amount, 'status' => $status,
                'status_label' => match ($status) { 'active' => 'Đang dùng', 'expired' => 'Đã hết hạn', 'trial' => 'Dùng thử', default => ucfirst($status) },
                'date' => $record->starts_at ?: $record->created_at, 'ref' => $record->payment_ref,
                'url' => community_route('membership'), 'action' => $status === 'active' ? 'Đổi hoặc gia hạn' : 'Mở gói',
            ]);
        }

        $enrollments = CourseEnrollment::with('course')->where('user_id', $userId)->latest('enrolled_at')->get();
        foreach ($enrollments as $enrollment) {
            $status = $enrollment->status === 'pending_payment' ? 'pending' : ($enrollment->status === 'active' ? 'active' : $enrollment->status);
            $orders->push((object) [
                'kind' => 'course', 'kind_label' => 'Khóa học', 'title' => $enrollment->course?->title ?: 'Khóa học đã lưu trữ',
                'amount' => (int) ($enrollment->amount_paid ?: $enrollment->course?->price), 'status' => $status,
                'status_label' => $status === 'pending' ? 'Chờ thanh toán' : ($enrollment->completed_at ? 'Đã hoàn thành' : 'Đang học'),
                'date' => $enrollment->enrolled_at ?: $enrollment->created_at, 'ref' => $enrollment->payment_ref,
                'url' => $enrollment->course ? community_route('academy.show', ['id' => $enrollment->course->id]) : null,
                'action' => $status === 'pending' ? 'Hoàn tất thanh toán' : 'Mở khóa học',
            ]);
        }

        $membershipsByChallenge = ExpeditionMember::with('expedition')->where('user_id', $userId)->latest('joined_at')->get();
        foreach ($membershipsByChallenge as $member) {
            $status = match (true) {
                $member->status === 'pending_payment' => 'pending',
                $member->status === 'rejected' || $member->kicked_at => 'expired',
                default => 'active',
            };
            $orders->push((object) [
                'kind' => 'challenge', 'kind_label' => 'Challenge', 'title' => $member->expedition?->title ?: 'Challenge đã lưu trữ',
                'amount' => (int) ($member->payment_amount ?: $member->expedition?->price), 'status' => $status,
                'status_label' => $member->completed_at ? 'Đã hoàn thành' : ($status === 'pending' ? 'Chờ thanh toán' : ($member->status === 'pending' ? 'Chờ duyệt' : 'Đang tham gia')),
                'date' => $member->joined_at, 'ref' => $member->payment_ref,
                'url' => $member->expedition ? community_route('challenge.show', ['slug' => $member->expedition->slug ?? $member->expedition->id]) : null,
                'action' => $status === 'pending' ? 'Mở Challenge' : 'Tiếp tục Challenge',
            ]);
        }

        $purchases = ProductPurchase::with('product')->where('user_id', $userId)->latest()->get();
        foreach ($purchases as $purchase) {
            $status = $purchase->status === 'pending_payment' ? 'pending' : $purchase->status;
            $orders->push((object) [
                'kind' => 'resource', 'kind_label' => 'Tài nguyên', 'title' => $purchase->product?->title ?: 'Sản phẩm đã lưu trữ',
                'amount' => (int) ($purchase->amount_paid ?: $purchase->product?->price), 'status' => $status,
                'status_label' => $status === 'pending' ? 'Chờ thanh toán' : 'Đã mua',
                'date' => $purchase->paid_at ?: $purchase->created_at, 'ref' => $purchase->payment_ref,
                'url' => community_route('marketplace'), 'action' => $status === 'pending' ? 'Thanh toán tiếp' : 'Mở Marketplace',
            ]);
        }

        $ownedCourseIds = $enrollments->where('status', 'active')->pluck('course_id');
        $ownedChallengeIds = $membershipsByChallenge->filter(fn ($member) => in_array($member->status, ['approved', 'paid'], true) && !$member->kicked_at)->pluck('expedition_id');
        $ownedProductIds = $purchases->where('status', 'active')->pluck('digital_product_id');
        $unowned = collect();

        Course::where('is_published', true)->get()->each(function ($course) use ($ownedCourseIds, $hasPremium, $unowned): void {
            if (!$hasPremium && !$ownedCourseIds->contains($course->id)) {
                $unowned->push((object) ['kind_label' => 'Khóa học', 'title' => $course->title, 'price' => (int) $course->price, 'url' => community_route('academy.show', ['id' => $course->id]), 'action' => 'Xem khóa học']);
            }
        });
        Expedition::whereIn('status', ['open', 'active'])->get()->each(function ($challenge) use ($ownedChallengeIds, $hasPremium, $unowned): void {
            if (!$hasPremium && !$ownedChallengeIds->contains($challenge->id)) {
                $unowned->push((object) ['kind_label' => 'Challenge', 'title' => $challenge->title, 'price' => (int) $challenge->price, 'url' => community_route('challenge.show', ['slug' => $challenge->slug ?? $challenge->id]), 'action' => 'Xem Challenge']);
            }
        });
        DigitalProduct::where('is_published', true)->get()->each(function ($product) use ($ownedProductIds, $unowned): void {
            if (!$ownedProductIds->contains($product->id)) {
                $unowned->push((object) ['kind_label' => 'Tài nguyên', 'title' => $product->title, 'price' => (int) $product->price, 'url' => community_route('marketplace'), 'action' => 'Mở Marketplace']);
            }
        });

        $visibleOrders = match ($this->tab) {
            'purchased' => $orders->whereIn('status', ['active', 'completed']),
            'pending' => $orders->where('status', 'pending'),
            'expired' => $orders->whereIn('status', ['expired', 'rejected', 'banned']),
            default => $orders,
        };

        return view('livewire.orders-page', [
            'membership' => $membership,
            'orders' => $visibleOrders,
            'allOrdersCount' => $orders->count(),
            'purchasedCount' => $orders->whereIn('status', ['active', 'completed'])->count(),
            'pendingCount' => $orders->where('status', 'pending')->count(),
            'unowned' => $unowned,
        ])->layout('layouts.app', ['title' => 'Gói & Đơn hàng — ' . brand()->name]);
    }
}
