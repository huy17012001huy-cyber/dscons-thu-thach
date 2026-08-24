<?php

namespace App\Console\Commands;

use App\Models\RecruiterCreditLedger;
use App\Models\RecruitmentContactRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireRecruitmentRequests extends Command
{
    protected $signature = 'recruitment:expire-requests';
    protected $description = 'Expire unanswered recruitment contact requests and refund reserved credits.';

    public function handle(): int
    {
        RecruitmentContactRequest::query()->where('status', 'pending')->where('created_at', '<', now()->subDays(7))->chunkById(100, function ($requests): void {
            foreach ($requests as $request) {
                DB::transaction(function () use ($request): void {
                    $request = RecruitmentContactRequest::query()->lockForUpdate()->find($request->id);
                    if (! $request || $request->status !== 'pending') return;
                    $request->update(['status' => 'expired', 'responded_at' => now()]);
                    $entitlement = $request->entitlement()->lockForUpdate()->first();
                    if ($entitlement && $entitlement->credits_reserved > 0) {
                        $entitlement->decrement('credits_reserved');
                        RecruiterCreditLedger::create(['brand_id' => $request->brand_id, 'entitlement_id' => $request->entitlement_id, 'recruiter_id' => $request->recruiter_id, 'amount' => 1, 'type' => 'refund', 'reference' => 'contact:'.$request->id]);
                    }
                });
            }
        });

        return self::SUCCESS;
    }
}
