<?php

namespace App\Livewire;

use App\Models\RecruitmentContactRequest;
use App\Models\User;
use App\Services\RecruiterContactService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class EngineerRecruitmentRequestsPage extends Component
{
    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User && $user->isEngineer(), 403);
        abort_unless(brand()->has_cv || (app()->environment('testing') && brand()->slug === 'dscons'), 404);
    }

    public function acceptRequest(int $requestId): void
    {
        $request = $this->requests()->where('status', 'pending')->findOrFail($requestId);
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        app(RecruiterContactService::class)->respond($request, $user, true);
        $this->dispatch('toast', message: 'Bạn đã chấp thuận yêu cầu liên hệ.', type: 'success');
    }

    public function rejectRequest(int $requestId): void
    {
        $request = $this->requests()->where('status', 'pending')->findOrFail($requestId);
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        app(RecruiterContactService::class)->respond($request, $user, false);
        $this->dispatch('toast', message: 'Đã từ chối yêu cầu và hoàn credit cho recruiter.', type: 'success');
    }

    /** @return Builder<RecruitmentContactRequest> */
    private function requests(): Builder
    {
        return RecruitmentContactRequest::query()
            ->where('brand_id', brand()->id)
            ->where('engineer_id', auth()->id())
            ->with(['recruiter.recruiterProfile', 'conversation'])
            ->latest();
    }

    public function render(): View
    {
        return view('livewire.engineer-recruitment-requests-page', [
            'requests' => $this->requests()->get(),
        ])->layout('layouts.app', ['title' => 'Yêu cầu tuyển dụng · '.brand()->name]);
    }
}
