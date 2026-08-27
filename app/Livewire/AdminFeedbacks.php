<?php

namespace App\Livewire;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class AdminFeedbacks extends Component
{
    use WithPagination;

    public string $filterType = '';

    public string $filterStatus = 'pending';

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function markReviewed(int $id): void
    {
        $this->authorizeAdmin();
        $this->feedbackQuery()->findOrFail($id)->update(['status' => 'reviewed']);
    }

    public function markResolved(int $id): void
    {
        $this->authorizeAdmin();
        $this->feedbackQuery()->findOrFail($id)->update(['status' => 'resolved']);
    }

    public function saveNotes(int $id, string $notes): void
    {
        $this->authorizeAdmin();
        $this->feedbackQuery()->findOrFail($id)->update(['admin_notes' => $notes]);
        $this->dispatch('toast', message: 'Đã lưu ghi chú.', type: 'success');
    }

    public function deleteFeedback(int $id): void
    {
        $this->authorizeAdmin();
        $this->feedbackQuery()->findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Đã xóa.', type: 'success');
    }

    public function render(): View
    {
        $feedbacks = $this->feedbackQuery()
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(20);

        return view('livewire.admin-feedbacks', ['feedbacks' => $feedbacks])
            ->layout('layouts.app', ['title' => 'Góp ý & Khiếu nại — Admin']);
    }

    private function authorizeAdmin(): void
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isBrandAdmin(app()->bound('brand') ? brand()->id : null), 403);
    }

    /** @return Builder<Feedback> */
    private function feedbackQuery(): Builder
    {
        return Feedback::with(['user', 'brand'])
            ->when(app()->bound('brand'), fn ($query) => $query->where('brand_id', brand()->id));
    }
}
