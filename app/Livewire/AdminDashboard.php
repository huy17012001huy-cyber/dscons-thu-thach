<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Livewire\Component;

class AdminDashboard extends Component
{
    public function render()
    {
        return view('livewire.admin-dashboard', [
            'totalUsers' => User::count(),
            'totalPosts' => Post::count(),
            'pendingReports' => Report::where('status', 'pending')->count(),
            'pendingCot' => Post::whereNotNull('cot_by')->where('is_cot', false)->count(),
        ])->layout('layouts.app', ['title' => 'Admin — Website Thử Thách']);
    }
}
