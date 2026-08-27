<?php

declare(strict_types=1);

namespace Modules\Recruitment\Application;

use App\Core\Audit\AuditLogger;
use App\Core\CommunityContext;
use App\Models\Conversation;
use App\Models\EngineerCv;
use App\Models\EngineerProfile;
use App\Models\RecruiterCreditLedger;
use App\Models\RecruiterEntitlement;
use App\Models\RecruitmentContactRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Recruitment\Notifications\RecruitmentNotification;

final class RecruiterContactService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly CommunityContext $context,
    ) {}

    public function request(User $recruiter, EngineerProfile $engineer, EngineerCv $cv, ?string $message = null): RecruitmentContactRequest
    {
        $brand = $this->context->current();
        abort_unless($recruiter->isRecruiter(), 403);
        abort_unless($brand && ($brand->has_recruitment || (app()->environment('testing') && $brand->slug === 'dscons')), 404);
        abort_if($recruiter->id === $engineer->user_id, 422);
        abort_unless((int) $engineer->brand_id === (int) $brand->id, 422);
        abort_unless($engineer->is_searchable, 422);
        abort_unless($cv->user_id === $engineer->user_id && $cv->brand_id === $engineer->brand_id && $cv->status === 'published', 422);

        return DB::transaction(function () use ($brand, $cv, $engineer, $message, $recruiter): RecruitmentContactRequest {
            $existing = RecruitmentContactRequest::query()
                ->where('brand_id', $brand->id)
                ->where('recruiter_id', $recruiter->id)
                ->where('engineer_id', $engineer->user_id)
                ->whereIn('status', ['pending', 'accepted'])
                ->lockForUpdate()
                ->first();
            if ($existing) {
                return $existing;
            }

            $entitlement = $this->availableEntitlement($brand->id, $recruiter->id);
            abort_unless($entitlement instanceof RecruiterEntitlement, 402, 'Bạn cần một credit liên hệ còn hiệu lực.');
            $entitlement->increment('credits_reserved');
            RecruiterCreditLedger::create([
                'brand_id' => $brand->id,
                'entitlement_id' => $entitlement->id,
                'recruiter_id' => $recruiter->id,
                'amount' => -1,
                'type' => 'reserve',
                'reference' => 'contact:'.$engineer->user_id,
            ]);
            $request = RecruitmentContactRequest::create([
                'brand_id' => $brand->id,
                'recruiter_id' => $recruiter->id,
                'engineer_id' => $engineer->user_id,
                'cv_id' => $cv->id,
                'entitlement_id' => $entitlement->id,
                'status' => 'pending',
                'message' => $message,
                'reserved_at' => now(),
            ]);
            $engineerUser = $engineer->user;
            abort_unless($engineerUser instanceof User, 500);
            DB::afterCommit(function () use ($brand, $engineerUser, $recruiter, $request): void {
                $engineerUser->notify(new RecruitmentNotification(
                    'Yêu cầu liên hệ tuyển dụng mới',
                    'Bạn có một yêu cầu liên hệ tuyển dụng mới trên '.$brand->name.'.',
                    community_route('engineer.cv'),
                ));
                $this->audit->record('recruitment', 'contact_request_created', $recruiter, $request, $brand->id);
            });

            return $request;
        });
    }

    public function respond(RecruitmentContactRequest $request, User $engineer, bool $accepted): void
    {
        DB::transaction(function () use ($accepted, $engineer, $request): void {
            $request = RecruitmentContactRequest::query()->lockForUpdate()->findOrFail($request->id);
            abort_unless($request->engineer_id === $engineer->id && $request->status === 'pending', 403);
            $brand = $this->context->current();
            abort_unless(! $brand || (int) $request->brand_id === (int) $brand->id, 403);
            $request->update([
                'status' => $accepted ? 'accepted' : 'rejected',
                'responded_at' => now(),
                'contact_revealed_at' => $accepted ? now() : null,
            ]);
            $this->settleCredit($request, $accepted);
            if ($accepted) {
                Conversation::updateOrCreate(
                    [
                        'brand_id' => $request->brand_id,
                        'user_one_id' => min($request->recruiter_id, $engineer->id),
                        'user_two_id' => max($request->recruiter_id, $engineer->id),
                        'conversation_type' => 'recruitment',
                    ],
                    ['contact_request_id' => $request->id, 'last_message_at' => now()],
                );
            }
            $recruiter = $request->recruiter;
            abort_unless($recruiter instanceof User, 500);
            DB::afterCommit(function () use ($accepted, $engineer, $recruiter, $request): void {
                $recruiter->notify(new RecruitmentNotification(
                    $accepted ? 'Ứng viên đã chấp thuận' : 'Yêu cầu liên hệ đã bị từ chối',
                    $accepted ? 'Ứng viên đã chấp thuận yêu cầu liên hệ của bạn.' : 'Ứng viên đã từ chối yêu cầu liên hệ của bạn.',
                    community_route('recruiter.dashboard'),
                ));
                $this->audit->record(
                    'recruitment',
                    $accepted ? 'contact_request_accepted' : 'contact_request_rejected',
                    $engineer,
                    $request,
                    $request->brand_id,
                );
            });
        });
    }

    private function availableEntitlement(int $brandId, int $recruiterId): ?RecruiterEntitlement
    {
        return RecruiterEntitlement::query()
            ->where('brand_id', $brandId)
            ->where('recruiter_id', $recruiterId)
            ->where('credits_total', '>', DB::raw('credits_reserved + credits_used'))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->orderBy('expires_at')
            ->lockForUpdate()
            ->first();
    }

    private function settleCredit(RecruitmentContactRequest $request, bool $accepted): void
    {
        $entitlement = $request->entitlement()->lockForUpdate()->first();
        if (! $entitlement) {
            return;
        }

        $entitlement->decrement('credits_reserved');
        if ($accepted) {
            $entitlement->increment('credits_used');

            return;
        }

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
