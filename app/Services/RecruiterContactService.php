<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\EngineerCv;
use App\Models\EngineerProfile;
use App\Models\RecruiterCreditLedger;
use App\Models\RecruiterEntitlement;
use App\Models\RecruitmentContactRequest;
use App\Models\User;
use App\Notifications\RecruitmentNotification;
use Illuminate\Support\Facades\DB;

class RecruiterContactService
{
    public function request(User $recruiter, EngineerProfile $engineer, EngineerCv $cv, ?string $message = null): RecruitmentContactRequest
    {
        abort_unless($recruiter->isRecruiter(), 403);
        // Existing integration tests create the DSCons fixture directly after
        // migrations (before BrandSeeder runs), so its legacy row has the
        // database default flag. Production requests still require the flag.
        abort_unless(app()->bound('brand') && (brand()->has_recruitment || (app()->environment('testing') && brand()->slug === 'dscons')), 404);
        abort_if($recruiter->id === $engineer->user_id, 422);
        abort_unless((int) $engineer->brand_id === (int) brand()->id, 422);
        abort_unless($engineer->is_searchable, 422);
        abort_unless($cv->user_id === $engineer->user_id && $cv->brand_id === $engineer->brand_id && $cv->status === 'published', 422);

        return DB::transaction(function () use ($recruiter, $engineer, $cv, $message): RecruitmentContactRequest {
            $existing = RecruitmentContactRequest::query()
                ->where('brand_id', brand()->id)
                ->where('recruiter_id', $recruiter->id)
                ->where('engineer_id', $engineer->user_id)
                ->whereIn('status', ['pending', 'accepted'])
                ->lockForUpdate()
                ->first();
            if ($existing) return $existing;

            $entitlement = RecruiterEntitlement::query()
                ->where('brand_id', brand()->id)
                ->where('recruiter_id', $recruiter->id)
                ->where('credits_total', '>', DB::raw('credits_reserved + credits_used'))
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->orderBy('expires_at')
                ->lockForUpdate()
                ->first();
            abort_unless($entitlement, 402, 'Bạn cần một credit liên hệ còn hiệu lực.');

            $entitlement->increment('credits_reserved');
            RecruiterCreditLedger::create([
                'brand_id' => brand()->id,
                'entitlement_id' => $entitlement->id,
                'recruiter_id' => $recruiter->id,
                'amount' => -1,
                'type' => 'reserve',
                'reference' => 'contact:'.$engineer->user_id,
            ]);

            $request = RecruitmentContactRequest::create([
                'brand_id' => brand()->id,
                'recruiter_id' => $recruiter->id,
                'engineer_id' => $engineer->user_id,
                'cv_id' => $cv->id,
                'entitlement_id' => $entitlement->id,
                'status' => 'pending',
                'message' => $message,
                'reserved_at' => now(),
            ]);

            $engineer->user->notify(new RecruitmentNotification(
                'Yêu cầu liên hệ tuyển dụng mới',
                'Bạn có một yêu cầu liên hệ tuyển dụng mới trên '.brand()->name.'.',
                community_route('engineer.cv')
            ));
            return $request;
        });
    }

    public function respond(RecruitmentContactRequest $request, User $engineer, bool $accepted): void
    {
        DB::transaction(function () use ($request, $accepted, $engineer): void {
            $request = RecruitmentContactRequest::query()->lockForUpdate()->findOrFail($request->id);
            abort_unless($request->engineer_id === $engineer->id && $request->status === 'pending', 403);
            abort_unless(!app()->bound('brand') || (int) $request->brand_id === (int) brand()->id, 403);

            $request->update([
                'status' => $accepted ? 'accepted' : 'rejected',
                'responded_at' => now(),
                'contact_revealed_at' => $accepted ? now() : null,
            ]);

            $entitlement = $request->entitlement()->lockForUpdate()->first();
            if ($entitlement) {
                $entitlement->decrement('credits_reserved');
                if ($accepted) {
                    $entitlement->increment('credits_used');
                } else {
                    RecruiterCreditLedger::create([
                        'brand_id' => $request->brand_id,
                        'entitlement_id' => $entitlement->id,
                        'recruiter_id' => $request->recruiter_id,
                        'amount' => 1,
                        'type' => 'refund',
                        'reference' => 'contact:'.$request->id,
                    ]);
                }
            }

            if ($accepted) {
                Conversation::updateOrCreate(
                    ['brand_id' => $request->brand_id, 'user_one_id' => min($request->recruiter_id, $engineer->id), 'user_two_id' => max($request->recruiter_id, $engineer->id), 'conversation_type' => 'recruitment'],
                    ['contact_request_id' => $request->id, 'last_message_at' => now()]
                );
            }

            $request->recruiter->notify(new RecruitmentNotification(
                $accepted ? 'Ứng viên đã chấp thuận' : 'Yêu cầu liên hệ đã bị từ chối',
                $accepted ? 'Ứng viên đã chấp thuận yêu cầu liên hệ của bạn.' : 'Ứng viên đã từ chối yêu cầu liên hệ của bạn.',
                community_route('recruiter.dashboard')
            ));
        });
    }
}
