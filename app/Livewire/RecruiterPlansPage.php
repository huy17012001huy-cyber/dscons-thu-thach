<?php

namespace App\Livewire;

use App\Models\RecruiterOrder;
use App\Models\RecruiterPlan;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Recruitment\Application\RecruiterPlanPurchaseService;

class RecruiterPlansPage extends Component
{
    public function purchase(int $planId): void
    {
        $user = $this->currentUser();
        $order = app(RecruiterPlanPurchaseService::class)->purchase(brand(), $user, $planId);
        $plan = $order->plan;
        $message = $plan instanceof RecruiterPlan && $plan->price > 0
            ? 'Đơn hàng đã tạo. Vui lòng hoàn tất thanh toán theo hướng dẫn.'
            : 'Gói đã được kích hoạt.';
        $this->dispatch('toast', message: $message, type: 'success');
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
