<?php

namespace App\Livewire;

use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Community\Application\CommunityModerationService;

class AdminReports extends Component
{
    public function dismiss(int $id): void
    {
        $this->authorizeModerator();
        app(CommunityModerationService::class)->dismissReport($id, $this->currentUser());
    }

    public function reviewed(int $id): void
    {
        $this->authorizeModerator();
        app(CommunityModerationService::class)->reviewReport($id, $this->currentUser());
    }

    public function deleteReportable(int $id): void
    {
        $this->authorizeModerator();
        app(CommunityModerationService::class)->deleteReportedContent($id, $this->currentUser());
    }

    public function render(): View
    {
        $this->authorizeModerator();
        $reports = Report::with(['user', 'reportable'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('livewire.admin-reports', ['reports' => $reports])
            ->layout('layouts.app', ['title' => 'Báo cáo — Admin']);
    }

    private function authorizeModerator(): void
    {
        abort_unless($this->currentUser()->isCommunityModerator(), 403);
    }

    private function currentUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
