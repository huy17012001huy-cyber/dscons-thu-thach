<?php

namespace App\Livewire;

use App\Models\RecruiterOrder;
use App\Models\RecruiterPlan;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RecruiterPlansPage extends Component
{
    public function purchase(int $planId): void
    {
        $plan = RecruiterPlan::where('is_active', true)->findOrFail($planId);
        DB::transaction(function () use ($plan): void {
            $order = RecruiterOrder::firstOrCreate(
                ['payment_ref' => 'RECPLAN'.$plan->id.'U'.auth()->id()],
                ['brand_id' => brand()->id, 'recruiter_id' => auth()->id(), 'plan_id' => $plan->id, 'status' => $plan->price > 0 ? 'pending_payment' : 'active', 'amount' => $plan->price, 'amount_paid' => $plan->price, 'paid_at' => $plan->price > 0 ? null : now()]
            );
            if ($plan->price === 0) {
                $order->entitlement()->firstOrCreate([], ['brand_id' => brand()->id, 'recruiter_id' => auth()->id(), 'credits_total' => $plan->contact_credits, 'starts_at' => now(), 'expires_at' => $plan->duration_days ? now()->addDays($plan->duration_days) : null]);
            }
        });
        $this->dispatch('toast', message: $plan->price > 0 ? 'Đơn hàng đã tạo. Vui lòng hoàn tất thanh toán theo hướng dẫn.' : 'Gói đã được kích hoạt.', type: 'success');
    }

    public function render()
    {
        return view('livewire.recruiter-plans-page', ['plans' => RecruiterPlan::where('is_active', true)->orderBy('price')->get(), 'orders' => RecruiterOrder::with('plan')->where('recruiter_id', auth()->id())->latest()->limit(10)->get()])->layout('layouts.recruiter', ['title' => 'Gói tuyển dụng']);
    }
}
