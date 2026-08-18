<?php
namespace App\Livewire;
use App\Services\XpService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
class SidebarMyXp extends Component {
    public function render() {
        $user = Auth::user();
        $xpService = app(XpService::class);
        return view('livewire.sidebar-my-xp', [
            'user'     => $user,
            'progress' => $xpService->expProgressPct($user),
            'toNext'   => $xpService->expToNextLevel($user),
        ]);
    }
}
