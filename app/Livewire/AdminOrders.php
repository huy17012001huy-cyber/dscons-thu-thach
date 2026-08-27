<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\DigitalProduct;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\ProductPurchase;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

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

        $reference = 'ADMIN-ACTIVATE-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5));
        $user = null;
        $label = 'đơn hàng';

        DB::transaction(function () use ($type, $id, $reference, &$user, &$label): void {
            if ($type === 'challenge') {
                $member = ExpeditionMember::with(['user', 'expedition'])->findOrFail($id);
                $user = $member->user;
                $expedition = $member->expedition;
                $label = $expedition ? $expedition->title : 'Challenge đã lưu trữ';
                $member->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => Auth::id(),
                    'payment_amount' => 0,
                    'payment_ref' => $member->payment_ref ?: $reference,
                    'kicked_at' => null,
                ]);
            } elseif ($type === 'course') {
                $enrollment = CourseEnrollment::with(['user', 'course'])->findOrFail($id);
                $user = $enrollment->user;
                $course = $enrollment->course;
                $label = $course ? $course->title : 'Khóa học đã lưu trữ';
                $enrollment->update([
                    'status' => 'active',
                    'amount_paid' => 0,
                    'payment_ref' => $enrollment->payment_ref ?: $reference,
                    'paid_at' => now(),
                ]);
            } elseif ($type === 'product') {
                $purchase = ProductPurchase::with(['user', 'product'])->findOrFail($id);
                $user = $purchase->user;
                $label = $purchase->product->title;
                $purchase->update([
                    'status' => 'active',
                    'amount_paid' => 0,
                    'payment_ref' => $purchase->payment_ref ?: $reference,
                    'paid_at' => now(),
                ]);
            }
        });

        if ($user instanceof User) {
            $user->notify(new GenericNotification('✓', 'Đơn hàng đã được Admin kích hoạt: '.$label));
        }
        $this->dispatch('toast', message: 'Đã kích hoạt '.$label, type: 'success');
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

        $reference = 'GIFT-ADMIN'.Auth::id().'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5));
        $user = User::findOrFail($this->grantUserId);
        $label = '';
        $url = null;

        DB::transaction(function () use ($reference, $user, &$label, &$url): void {
            if ($this->grantType === 'challenge') {
                $resource = Expedition::findOrFail($this->grantResourceId);
                $member = ExpeditionMember::withoutGlobalScopes()->firstOrNew([
                    'expedition_id' => $resource->id,
                    'user_id' => $user->id,
                ]);
                $member->fill([
                    'brand_id' => brand()->id,
                    'class_at_join' => $member->class_at_join ?: $user->class,
                    'joined_at' => $member->joined_at ?: now(),
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => Auth::id(),
                    'payment_amount' => 0,
                    'payment_ref' => $reference,
                    'kicked_at' => null,
                    'personal_starts_at' => null,
                ])->save();
                $label = $resource->title;
                $url = route('challenge.show', $resource->slug);
            } elseif ($this->grantType === 'course') {
                $resource = Course::findOrFail($this->grantResourceId);
                $enrollment = CourseEnrollment::withoutGlobalScopes()->firstOrNew([
                    'user_id' => $user->id,
                    'course_id' => $resource->id,
                ]);
                $enrollment->fill([
                    'brand_id' => brand()->id,
                    'status' => 'active',
                    'payment_ref' => $reference,
                    'amount_paid' => 0,
                    'paid_at' => now(),
                    'enrolled_at' => $enrollment->enrolled_at ?: now(),
                ])->save();
                $label = $resource->title;
                $url = community_route('academy.show', ['id' => $resource->id]);
            } else {
                $resource = DigitalProduct::findOrFail($this->grantResourceId);
                $purchase = ProductPurchase::withoutGlobalScopes()->firstOrNew([
                    'user_id' => $user->id,
                    'digital_product_id' => $resource->id,
                ]);
                $purchase->fill([
                    'brand_id' => brand()->id,
                    'status' => 'active',
                    'payment_ref' => $reference,
                    'amount_paid' => 0,
                    'paid_at' => now(),
                ])->save();
                $label = $resource->title;
            }
        });

        $user->notify(new GenericNotification('🎁', 'Bạn được tặng quyền truy cập: '.$label, $url));
        $this->reset(['grantResourceId']);
        $this->dispatch('toast', message: 'Đã tặng quyền cho '.$user->name, type: 'success');
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
