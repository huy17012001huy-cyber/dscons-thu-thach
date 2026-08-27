<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\CourseEnrollment;
use App\Models\ExpeditionMember;
use App\Models\Membership;
use App\Models\ProductPurchase;
use App\Models\RecruiterOrder;
use App\Models\User;
use App\Models\UserBillingProfile;
use App\Models\UserCommunityPreference;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AccountSettings extends Component
{
    public string $invoiceType = 'personal';

    public string $fullName = '';

    public string $companyName = '';

    public string $invoiceEmail = '';

    public string $identityNumber = '';

    public string $taxCode = '';

    public string $address = '';

    public string $phone = '';

    /** @var array<int, bool> */
    public array $notificationStates = [];

    public function mount(): void
    {
        abort_unless(Auth::user() instanceof User, 403);
        $this->loadInvoiceProfile();
        $this->loadNotificationStates();
    }

    public function setInvoiceType(string $type): void
    {
        if (! in_array($type, ['personal', 'company'], true)) {
            return;
        }

        $this->invoiceType = $type;
        $this->loadInvoiceProfile();
    }

    public function saveBilling(): void
    {
        $rules = [
            'invoiceEmail' => 'required|email|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:40',
        ];

        if ($this->invoiceType === 'personal') {
            $rules['fullName'] = 'required|string|min:2|max:160';
            $rules['identityNumber'] = 'nullable|string|max:40';
        } else {
            $rules['companyName'] = 'required|string|min:2|max:200';
            $rules['taxCode'] = 'required|string|max:40';
        }

        $this->validate($rules);

        UserBillingProfile::updateOrCreate(
            ['user_id' => Auth::id(), 'type' => $this->invoiceType],
            [
                'full_name' => $this->invoiceType === 'personal' ? trim($this->fullName) : null,
                'company_name' => $this->invoiceType === 'company' ? trim($this->companyName) : null,
                'invoice_email' => trim($this->invoiceEmail),
                'identity_number' => $this->invoiceType === 'personal' ? trim($this->identityNumber) ?: null : null,
                'tax_code' => $this->invoiceType === 'company' ? trim($this->taxCode) : null,
                'address' => trim($this->address) ?: null,
                'phone' => trim($this->phone) ?: null,
            ]
        );

        $this->dispatch('toast', message: 'Đã lưu thông tin xuất hóa đơn.', type: 'success');
    }

    public function toggleNotifications(int $brandId): void
    {
        abort_unless($this->availableBrands()->contains('id', $brandId), 403);

        $preference = UserCommunityPreference::withoutGlobalScopes()->firstOrCreate(
            ['user_id' => Auth::id(), 'brand_id' => $brandId],
            ['notifications_enabled' => true]
        );
        $preference->update(['notifications_enabled' => ! $preference->notifications_enabled]);
        $this->notificationStates[$brandId] = $preference->notifications_enabled;
    }

    public function render(): View
    {
        return view('livewire.account-settings', [
            'user' => Auth::user(),
            'communities' => $this->availableBrands(),
            'orders' => $this->purchaseHistory(),
        ])->layout('layouts.app', ['title' => 'Cài đặt tài khoản · '.brand()->name]);
    }

    private function loadInvoiceProfile(): void
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);
        $profile = UserBillingProfile::where('user_id', $user->id)
            ->where('type', $this->invoiceType)
            ->first();

        $this->fill([
            'fullName' => data_get($profile, 'full_name', ''),
            'companyName' => data_get($profile, 'company_name', ''),
            'invoiceEmail' => data_get($profile, 'invoice_email', $user->email),
            'identityNumber' => data_get($profile, 'identity_number', ''),
            'taxCode' => data_get($profile, 'tax_code', ''),
            'address' => data_get($profile, 'address', ''),
            'phone' => data_get($profile, 'phone', ''),
        ]);
    }

    private function loadNotificationStates(): void
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);
        $preferences = UserCommunityPreference::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->pluck('notifications_enabled', 'brand_id');

        foreach ($this->availableBrands() as $community) {
            $this->notificationStates[$community->id] = (bool) ($preferences[$community->id] ?? true);
        }
    }

    /** @return Collection<int, Brand> */
    private function availableBrands(): Collection
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);
        if ($user->is_admin) {
            return Brand::query()->where('status', 'active')->orderBy('name')->get();
        }

        return $user->brandRoles()
            ->wherePivotIn('role', ['member', 'moderator', 'admin', 'owner'])
            ->where('brands.status', 'active')
            ->orderBy('brands.name')
            ->get();
    }

    /** @return Collection<int, mixed> */
    private function purchaseHistory(): Collection
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);
        $userId = $user->id;
        /** @var Collection<int, array<string, mixed>> $items */
        $items = collect();

        Membership::withoutGlobalScopes()->with('brand')->where('user_id', $userId)->latest()->get()->each(function (Membership $item) use ($items): void {
            $items->push($this->historyItem($item->brand, 'membership', $item->plan ?: ucfirst($item->tier ?: 'Membership'), $item->paid_amount, $item->status, $item->starts_at ?: $item->created_at, $item->payment_ref));
        });

        CourseEnrollment::withoutGlobalScopes()->with(['course' => fn ($query) => $query->withoutGlobalScopes()->with('brand')])->where('user_id', $userId)->latest('enrolled_at')->get()->each(function (CourseEnrollment $item) use ($items): void {
            $course = $item->course;
            $items->push($this->historyItem($course?->brand, 'course', $course?->title ?: 'Khóa học đã lưu trữ', $item->amount_paid ?: $course?->price, $item->status, $item->enrolled_at ?: $item->created_at, $item->payment_ref));
        });

        ExpeditionMember::withoutGlobalScopes()->with(['expedition' => fn ($query) => $query->withoutGlobalScopes()->with('brand')])->where('user_id', $userId)->latest('joined_at')->get()->each(function (ExpeditionMember $item) use ($items): void {
            $expedition = $item->expedition;
            $items->push($this->historyItem($expedition?->brand, 'challenge', $expedition?->title ?: 'Challenge đã lưu trữ', $item->payment_amount ?: $expedition?->price, $item->status, $item->joined_at ?: $item->created_at, $item->payment_ref));
        });

        ProductPurchase::withoutGlobalScopes()->with(['product' => fn ($query) => $query->withoutGlobalScopes()->with('brand')])->where('user_id', $userId)->latest()->get()->each(function (ProductPurchase $item) use ($items): void {
            $product = $item->product;
            $type = $product->product_kind === 'revit_tool' ? 'revit_tool' : 'resource';
            $items->push($this->historyItem($product->brand, $type, $product->title, $item->amount_paid ?: $product->price, $item->status, $item->paid_at ?: $item->created_at, $item->payment_ref));
        });

        if ($user->isRecruiter()) {
            RecruiterOrder::withoutGlobalScopes()->with(['plan' => fn ($query) => $query->withoutGlobalScopes()->with('brand')])->where('recruiter_id', $userId)->latest()->get()->each(function (RecruiterOrder $item) use ($items): void {
                $plan = $item->plan;
                $items->push($this->historyItem($plan?->brand, 'recruiter', $plan?->name ?: 'Gói tuyển dụng', $item->amount_paid ?: $item->amount, $item->status, $item->paid_at ?: $item->created_at, $item->payment_ref));
            });
        }

        return $items->filter(fn (array $item) => $item['community'] !== null)->sortByDesc('date')->values();
    }

    /** @return array<string, mixed> */
    private function historyItem(?Brand $community, string $type, string $title, int|float|string|null $amount, string $status, ?CarbonInterface $date, ?string $reference): array
    {
        return [
            'type' => $type,
            'title' => $title,
            'community' => $community,
            'amount' => (int) $amount,
            'status' => $status,
            'date' => $date,
            'reference' => $reference,
        ];
    }
}
