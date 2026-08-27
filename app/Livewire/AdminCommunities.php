<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\CommunityApplication;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class AdminCommunities extends Component
{
    public string $filter = 'pending';

    public string $reviewNote = '';

    public function approve(int $id): void
    {
        $admin = $this->currentSuperAdmin();
        $application = CommunityApplication::query()->findOrFail($id);
        abort_if($application->status !== 'pending', 422, 'Hồ sơ đã được xử lý.');

        DB::transaction(function () use ($application, $admin): void {
            $brand = Brand::create([
                'name' => $application->name,
                'slug' => $application->slug,
                'domain' => $application->slug.'.local',
                'logo_path' => $application->logo_path,
                'banner_path' => $application->banner_path,
                'tagline' => $application->tagline,
                'description' => $application->description,
                'owner_id' => $application->applicant_id,
                'status' => 'active',
                'verified_at' => now(),
                'theme_primary' => '#1F77BE',
                'theme_accent' => '#E1F4F7',
                'theme_bg' => '#F7FAFC',
            ]);

            DB::table('brand_user')->insert([
                'brand_id' => $brand->id,
                'user_id' => $application->applicant_id,
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Membership::withoutGlobalScopes()->create([
                'brand_id' => $brand->id,
                'user_id' => $application->applicant_id,
                'tier' => 'free',
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addYears(10),
            ]);

            foreach ([
                [
                    'brand_id' => $brand->id, 'tier' => 'free', 'name' => 'Free', 'price' => 0,
                    'benefits' => ['Nội dung công khai', 'Feed cộng đồng'], 'status' => 'published',
                ],
                [
                    'brand_id' => $brand->id, 'tier' => 'premium', 'name' => 'Premium',
                    'price' => $application->proposed_premium_price ?? 0,
                    'benefits' => ['Toàn bộ khóa học', 'Challenge và sự kiện premium'], 'status' => 'pending_review',
                    'sepay_account' => $application->proposed_sepay_account,
                    'sepay_bank' => $application->proposed_sepay_bank,
                ],
            ] as $planData) {
                MembershipPlan::withoutGlobalScopes()->create($planData);
            }

            $application->update(['status' => 'approved', 'reviewed_by' => $admin->id, 'review_note' => $this->reviewNote ?: null]);
        });

        $this->reviewNote = '';
        $this->dispatch('toast', message: 'Đã duyệt và tạo cộng đồng.', type: 'success');
    }

    public function reject(int $id): void
    {
        $admin = $this->currentSuperAdmin();
        $application = CommunityApplication::query()->findOrFail($id);
        $application->update(['status' => 'rejected', 'reviewed_by' => $admin->id, 'review_note' => $this->reviewNote ?: null]);
        $this->reviewNote = '';
        $this->dispatch('toast', message: 'Đã từ chối hồ sơ.', type: 'success');
    }

    public function render(): View
    {
        $applications = CommunityApplication::query()
            ->with('applicant:id,name,email')
            ->when($this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
            ->latest()->get();
        $communities = Brand::query()->withCount('users')->with('owner:id,name')->latest()->get();

        return view('livewire.admin-communities', compact('applications', 'communities'))
            ->layout('layouts.app', ['title' => 'Quản lý cộng đồng']);
    }

    private function currentSuperAdmin(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        return $user;
    }
}
