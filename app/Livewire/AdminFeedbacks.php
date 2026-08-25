<?php

namespace App\Livewire;

use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;
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

    public function render()
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
        abort_unless(Auth::user()?->isBrandAdmin(app()->bound('brand') ? brand()->id : null), 403);
    }

    private function feedbackQuery()
    {
        return Feedback::with(['user', 'brand'])
            ->when(app()->bound('brand'), fn ($query) => $query->where('brand_id', brand()->id));
    }
}
