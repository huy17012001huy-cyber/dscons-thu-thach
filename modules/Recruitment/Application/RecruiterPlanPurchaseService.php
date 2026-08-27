<?php

declare(strict_types=1);

namespace Modules\Recruitment\Application;

use App\Core\CommunityContext;
use App\Models\Brand;
use App\Models\RecruiterOrder;
use App\Models\RecruiterPlan;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class RecruiterPlanPurchaseService
{
    public function __construct(private readonly CommunityContext $context) {}

    public function purchase(Brand $community, User $user, int $planId): RecruiterOrder
    {
        if (! $this->context->current()?->is($community)) {
            throw new AuthorizationException('Recruiter purchase must use the current community.');
        }

        return DB::transaction(function () use ($community, $user, $planId): RecruiterOrder {
            $plan = RecruiterPlan::query()
                ->where('brand_id', $community->id)
                ->where('is_active', true)
                ->find($planId);
            if (! $plan) {
                throw (new ModelNotFoundException)->setModel(RecruiterPlan::class, [$planId]);
            }

            $order = RecruiterOrder::query()->firstOrCreate(
                ['payment_ref' => 'RECPLAN'.$plan->id.'U'.$user->id],
                [
                    'brand_id' => $community->id,
                    'recruiter_id' => $user->id,
                    'plan_id' => $plan->id,
                    'status' => $plan->price > 0 ? 'pending_payment' : 'active',
                    'amount' => $plan->price,
                    'amount_paid' => $plan->price,
                    'paid_at' => $plan->price > 0 ? null : now(),
                ],
            );
            if ($plan->price === 0) {
                $order->entitlement()->firstOrCreate([], [
                    'brand_id' => $community->id,
                    'recruiter_id' => $user->id,
                    'credits_total' => $plan->contact_credits,
                    'starts_at' => now(),
                    'expires_at' => $plan->duration_days ? now()->addDays($plan->duration_days) : null,
                ]);
            }

            return $order->load('plan');
        });
    }
}
