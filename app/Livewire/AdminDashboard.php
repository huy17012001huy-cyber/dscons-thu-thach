<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;

class AdminDashboard extends Component
{
    public function render(): View
    {
        return view('livewire.admin-dashboard', [
            'totalUsers' => User::count(),
            'totalPosts' => Post::count(),
            'pendingReports' => Report::where('status', 'pending')->count(),
            'pendingCot' => Post::whereNotNull('cot_by')->where('is_cot', false)->count(),
        ])->layout('layouts.app', ['title' => 'Admin — DSCons']);
    }
}
