<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\DigitalProduct;
use App\Models\Expedition;
use App\Models\ProductPurchase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Commerce\Application\ProductPurchaseOutcome;
use Modules\Commerce\Application\ProductPurchaseService;

class MarketplacePage extends Component
{
    public function purchase(int $productId): void
    {
        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();
        if (! $user instanceof User) {
            return;
        }
        $outcome = app(ProductPurchaseService::class)->purchase($productId, $user);
        match ($outcome) {
            ProductPurchaseOutcome::Activated => $this->dispatch('toast', message: 'Đã nhận sản phẩm miễn phí.', type: 'success'),
            ProductPurchaseOutcome::PendingPayment => $this->dispatch('toast', message: 'Đơn hàng đã tạo. Vui lòng chuyển khoản để hoàn tất.', type: 'info'),
            ProductPurchaseOutcome::AlreadyOwned => $this->dispatch('toast', message: 'Bạn đã sở hữu sản phẩm này.', type: 'info'),
            ProductPurchaseOutcome::AlreadyPending => $this->dispatch('toast', message: 'Đơn hàng đang chờ thanh toán.', type: 'info'),
        };
    }

    public function render(): View
    {
        $user = Auth::user();
        $userId = $user instanceof User ? $user->id : null;
        $hasPremium = $user instanceof User && $user->hasPremiumMembership();

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
            $owned = $hasPremium || ($member && in_array($member->status, ['approved', 'paid'], true) && ! $member->kicked_at);
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
            $isRevitTool = $product->product_kind === 'revit_tool';
            $items->push((object) [
                'kind' => $isRevitTool ? 'revit_tool' : 'resource', 'kind_label' => $isRevitTool ? 'Revit Tool' : 'Tài nguyên', 'id' => $product->id,
                'title' => $product->title, 'description' => $product->description,
                'image' => $product->thumbnail ? asset('storage/'.$product->thumbnail) : null,
                'price' => (int) $product->price, 'pillar' => $product->pillar,
                'difficulty' => null, 'member_count' => (int) $product->active_purchases_count,
                'meta' => $isRevitTool ? (($versions = $product->supported_revit_versions ?: []) ? 'Revit '.implode(', ', $versions) : 'Chờ công bố phiên bản đã test runtime') : ($product->delivery_type === 'both' ? 'File + Link' : ($product->delivery_type === 'link' ? 'Link truy cập' : 'File tải xuống')),
                'featured' => (bool) $product->is_featured, 'owned' => $purchase?->status === 'active', 'pending' => $purchase?->status === 'pending_payment',
                'created_at' => $product->created_at, 'url' => null, 'purchase_id' => $product->id,
            ]);
        }

        return view('livewire.marketplace-page', [
            'items' => $items,
            'challengeItems' => $items->where('kind', 'challenge')->values(),
            'courseItems' => $items->where('kind', 'course')->values(),
            'resourceItems' => $items->where('kind', 'resource')->values(),
            'revitToolItems' => $items->where('kind', 'revit_tool')->values(),
        ])->layout('layouts.app', ['title' => 'Marketplace — '.brand()->name]);
    }
}
