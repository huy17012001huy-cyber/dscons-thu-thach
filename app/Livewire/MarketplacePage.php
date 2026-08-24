<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\DigitalProduct;
use App\Models\Expedition;
use App\Models\ProductPurchase;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MarketplacePage extends Component
{
    public function purchase(int $productId): void
    {
        if (!Auth::check()) return;

        $user = Auth::user();
        $product = DigitalProduct::where('is_published', true)->findOrFail($productId);
        $existing = ProductPurchase::where('user_id', $user->id)
            ->where('digital_product_id', $product->id)
            ->first();

        if ($existing) {
            $this->dispatch('toast', message: $existing->status === 'active' ? 'Bạn đã sở hữu sản phẩm này.' : 'Đơn hàng đang chờ thanh toán.', type: 'info');
            return;
        }

        ProductPurchase::create([
            'user_id' => $user->id,
            'digital_product_id' => $product->id,
            'status' => $product->isFree() ? 'active' : 'pending_payment',
            'paid_at' => $product->isFree() ? now() : null,
        ]);

        $this->dispatch('toast', message: $product->isFree() ? 'Đã nhận sản phẩm miễn phí.' : 'Đơn hàng đã tạo. Vui lòng chuyển khoản để hoàn tất.', type: $product->isFree() ? 'success' : 'info');
    }

    public function render()
    {
        $user = Auth::user();
        $userId = $user?->id;
        $hasPremium = $user?->hasPremiumMembership() ?? false;

        $ownedProducts = $userId
            ? ProductPurchase::where('user_id', $userId)->get()->keyBy('digital_product_id')
            : collect();

        $items = collect();

        $challenges = Expedition::query()
            ->whereIn('status', ['open', 'active'])
            ->withCount([
                'members as active_members_count' => fn ($q) => $q->whereIn('status', ['approved', 'paid'])->whereNull('kicked_at'),
                'tasks',
            ])
            ->with(['members' => fn ($q) => $q->when($userId, fn ($member) => $member->where('user_id', $userId))])
            ->latest()
            ->get();

        foreach ($challenges as $challenge) {
            $member = $challenge->members->first();
            $owned = $hasPremium || ($member && in_array($member->status, ['approved', 'paid'], true) && !$member->kicked_at);
            $pending = $member && in_array($member->status, ['pending', 'pending_payment'], true);
            $items->push((object) [
                'kind' => 'challenge', 'kind_label' => 'Challenge', 'id' => $challenge->id,
                'title' => $challenge->title, 'description' => $challenge->description,
                'image' => $challenge->cover_path ? asset('storage/'.$challenge->cover_path) : null,
                'price' => (int) $challenge->price, 'pillar' => null,
                'difficulty' => $challenge->difficulty_label, 'member_count' => (int) $challenge->active_members_count,
                'meta' => $challenge->required_days.' ngày · '.$challenge->tasks_count.' bài',
                'featured' => (bool) $challenge->is_featured, 'owned' => $owned, 'pending' => $pending,
                'created_at' => $challenge->created_at, 'url' => community_route('challenge.show', ['slug' => $challenge->slug ?? $challenge->id]),
                'purchase_id' => null,
            ]);
        }

        $courses = Course::query()
            ->where('is_published', true)
            ->withCount(['enrollments', 'modules'])
            ->with(['enrollments' => fn ($q) => $q->when($userId, fn ($enrollment) => $enrollment->where('user_id', $userId))])
            ->latest()
            ->get();

        foreach ($courses as $course) {
            $enrollment = $course->enrollments->first();
            $owned = $hasPremium || ($enrollment && $enrollment->status === 'active');
            $pending = $enrollment && $enrollment->status === 'pending_payment';
            $items->push((object) [
                'kind' => 'course', 'kind_label' => 'Khóa học', 'id' => $course->id,
                'title' => $course->title, 'description' => $course->description,
                'image' => $course->thumbnail ? asset('storage/'.$course->thumbnail) : null,
                'price' => (int) $course->price, 'pillar' => $course->pillar,
                'difficulty' => ucfirst($course->difficulty), 'member_count' => (int) $course->enrollments_count,
                'meta' => $course->modules_count.' module · '.$course->xp_reward.' XP',
                'featured' => (bool) $course->is_featured, 'owned' => $owned, 'pending' => $pending,
                'created_at' => $course->created_at, 'url' => community_route('academy.show', ['id' => $course->id]),
                'purchase_id' => null,
            ]);
        }

        $products = DigitalProduct::query()
            ->where('is_published', true)
            ->withCount(['purchases as active_purchases_count' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('sort_order')->latest()->get();

        foreach ($products as $product) {
            $purchase = $ownedProducts->get($product->id);
            $items->push((object) [
                'kind' => 'resource', 'kind_label' => 'Tài nguyên', 'id' => $product->id,
                'title' => $product->title, 'description' => $product->description,
                'image' => $product->thumbnail ? asset('storage/'.$product->thumbnail) : null,
                'price' => (int) $product->price, 'pillar' => $product->pillar,
                'difficulty' => null, 'member_count' => (int) $product->active_purchases_count,
                'meta' => $product->delivery_type === 'both' ? 'File + Link' : ($product->delivery_type === 'link' ? 'Link truy cập' : 'File tải xuống'),
                'featured' => (bool) $product->is_featured, 'owned' => $purchase?->status === 'active', 'pending' => $purchase?->status === 'pending_payment',
                'created_at' => $product->created_at, 'url' => null, 'purchase_id' => $product->id,
            ]);
        }

        return view('livewire.marketplace-page', [
            'items' => $items,
            'challengeItems' => $items->where('kind', 'challenge')->values(),
            'courseItems' => $items->where('kind', 'course')->values(),
            'resourceItems' => $items->where('kind', 'resource')->values(),
        ])->layout('layouts.app', ['title' => 'Marketplace — ' . brand()->name]);
    }
}
