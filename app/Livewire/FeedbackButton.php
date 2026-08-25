<?php

namespace App\Livewire;

use App\Models\Feedback;
use Livewire\Attributes\Rule;
use Livewire\Component;

class FeedbackButton extends Component
{
    public bool $showModal = false;

    #[Rule('required|in:khieu_nai,gop_y,bao_loi,thanh_toan,khac')]
    public string $type = 'gop_y';

    #[Rule('required|max:255')]
    public string $subject = '';

    #[Rule('required|min:10|max:5000')]
    public string $content = '';

    public function openModal(): void
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['type', 'subject', 'content']);
        $this->resetValidation();
    }

    public function submit(): void
    {
        $this->validate();

        Feedback::create([
            'user_id' => auth()->id(),
            'brand_id' => app()->bound('brand') ? brand()->id : null,
            'type' => $this->type,
            'subject' => $this->subject,
            'content' => $this->content,
        ]);

        $this->closeModal();
        $this->dispatch('toast', message: 'Gửi thành công! Cảm ơn bạn đã góp ý.', type: 'success');
    }

    public function render()
    {
        return view('livewire.feedback-button');
    }
}
