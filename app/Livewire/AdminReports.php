<?php

namespace App\Livewire;

use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AdminReports extends Component
{
    public function dismiss(int $id): void
    {
        if (!Auth::user()?->is_admin) return;
        Report::findOrFail($id)->update(['status' => 'dismissed']);
    }

    public function reviewed(int $id): void
    {
        if (!Auth::user()?->is_admin) return;
        Report::findOrFail($id)->update(['status' => 'reviewed']);
    }

    public function deleteReportable(int $id): void
    {
        if (!Auth::user()?->is_admin) return;
        $report = Report::findOrFail($id);
        $reportable = $report->reportable;
        if ($reportable) {
            $reportable->delete();
        }
        $report->update(['status' => 'reviewed']);
    }

    public function render()
    {
        $reports = Report::with(['user', 'reportable'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('livewire.admin-reports', ['reports' => $reports])
            ->layout('layouts.app', ['title' => 'Báo cáo — Admin']);
    }
}
