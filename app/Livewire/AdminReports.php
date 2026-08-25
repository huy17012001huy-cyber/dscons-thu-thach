<?php

namespace App\Livewire;

use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AdminReports extends Component
{
    public function dismiss(int $id): void
    {
        $this->authorizeModerator();
        Report::findOrFail($id)->update(['status' => 'dismissed']);
    }

    public function reviewed(int $id): void
    {
        $this->authorizeModerator();
        Report::findOrFail($id)->update(['status' => 'reviewed']);
    }

    public function deleteReportable(int $id): void
    {
        $this->authorizeModerator();
        $report = Report::findOrFail($id);
        $reportable = $report->reportable;
        if ($reportable) {
            $reportable->delete();
        }
        $report->update(['status' => 'reviewed']);
    }

    public function render()
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
        abort_unless(Auth::user()?->isCommunityModerator(), 403);
    }
}
