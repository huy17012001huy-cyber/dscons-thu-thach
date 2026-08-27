<?php

namespace App\Livewire;

use App\Models\RecruiterOrder;
use App\Models\RecruiterPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class RecruiterPlansPage extends Component
{
    public function purchase(int $planId): void
    {
        $user = $this->currentUser();
        $plan = RecruiterPlan::where('brand_id', brand()->id)->where('is_active', true)->findOrFail($planId);
        DB::transaction(function () use ($plan, $user): void {
            $order = RecruiterOrder::firstOrCreate(
                ['payment_ref' => 'RECPLAN'.$plan->id.'U'.$user->id],
                ['brand_id' => brand()->id, 'recruiter_id' => $user->id, 'plan_id' => $plan->id, 'status' => $plan->price > 0 ? 'pending_payment' : 'active', 'amount' => $plan->price, 'amount_paid' => $plan->price, 'paid_at' => $plan->price > 0 ? null : now()]
            );
            if ($plan->price === 0) {
                $order->entitlement()->firstOrCreate([], ['brand_id' => brand()->id, 'recruiter_id' => $user->id, 'credits_total' => $plan->contact_credits, 'starts_at' => now(), 'expires_at' => $plan->duration_days ? now()->addDays($plan->duration_days) : null]);
            }
        });
        $this->dispatch('toast', message: $plan->price > 0 ? 'Đơn hàng đã tạo. Vui lòng hoàn tất thanh toán theo hướng dẫn.' : 'Gói đã được kích hoạt.', type: 'success');
    }

    public function render(): View
    {
        $user = $this->currentUser();

        return view('livewire.recruiter-plans-page', ['plans' => RecruiterPlan::where('brand_id', brand()->id)->where('is_active', true)->orderBy('price')->get(), 'orders' => RecruiterOrder::with('plan')->where('brand_id', brand()->id)->where('recruiter_id', $user->id)->latest()->limit(10)->get()])->layout('layouts.recruiter', ['title' => 'Gói tuyển dụng']);
    }

    private function currentUser(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
