<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\DigitalProduct;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\ProductPurchase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Commerce\Application\AdminCommerceOrderService;

class AdminOrders extends Component
{
    public string $search = '';

    public string $grantUserSearch = '';

    public string $grantType = 'challenge';

    public ?int $grantUserId = null;

    public ?int $grantResourceId = null;

    public function updatedGrantType(): void
    {
        $this->grantResourceId = null;
    }

    public function activateOrder(string $type, int $id): void
    {
        if (! Auth::user()?->isBrandAdmin()) {
            return;
        }

        $result = app(AdminCommerceOrderService::class)->activate($type, $id, Auth::user());
        if (! $result) {
            return;
        }
        $this->dispatch('toast', message: 'Đã kích hoạt '.$result->label, type: 'success');
    }

    public function grantAccess(): void
    {
        if (! Auth::user()?->isBrandAdmin()) {
            return;
        }

        $this->validate([
            'grantUserId' => 'required|integer|exists:users,id',
            'grantType' => 'required|in:challenge,course,product',
            'grantResourceId' => 'required|integer',
        ]);

        $result = app(AdminCommerceOrderService::class)->grant(
            $this->grantType,
            (int) $this->grantUserId,
            (int) $this->grantResourceId,
            Auth::user(),
        );
        if (! $result) {
            return;
        }

        $this->reset(['grantResourceId']);
        $this->dispatch('toast', message: 'Đã tặng quyền cho '.$result->user->name, type: 'success');
    }

    public function render(): View
    {
        $term = trim($this->search);
        $pendingChallenges = ExpeditionMember::with(['user', 'expedition'])
            ->whereIn('status', ['pending', 'pending_payment'])
            ->when($term, fn ($query) => $query->whereHas('user', fn ($user) => $user
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")))
            ->latest('joined_at')
            ->limit(30)
            ->get();
        $pendingCourses = CourseEnrollment::with(['user', 'course'])
            ->where('status', 'pending_payment')
            ->when($term, fn ($query) => $query->whereHas('user', fn ($user) => $user
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")))
            ->latest('enrolled_at')
            ->limit(30)
            ->get();
        $pendingProducts = ProductPurchase::with(['user', 'product'])
            ->where('status', 'pending_payment')
            ->when($term, fn ($query) => $query->whereHas('user', fn ($user) => $user
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")))
            ->latest()
            ->limit(30)
            ->get();

        $grantUsers = User::query()
            ->where('account_type', '!=', 'recruiter')
            ->when(trim($this->grantUserSearch), function ($query) {
                $term = trim($this->grantUserSearch);
                $query->where(fn ($q) => $q
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('username', 'like', "%{$term}%"));
            })
            ->orderBy('name')
            ->limit(30)
            ->get(['id', 'name', 'email', 'username']);

        $grantResources = match ($this->grantType) {
            'course' => Course::query()->where('is_published', true)->orderBy('title')->get(['id', 'title']),
            'product' => DigitalProduct::query()->where('is_published', true)->orderBy('title')->get(['id', 'title']),
            default => Expedition::query()->whereIn('status', ['open', 'active'])->orderBy('title')->get(['id', 'title']),
        };

        return view('livewire.admin-orders', compact(
            'pendingChallenges', 'pendingCourses', 'pendingProducts', 'grantUsers', 'grantResources'
        ))->layout('layouts.app', ['title' => 'Đơn hàng & cấp quyền — Admin']);
    }
}
