<?php

namespace App\Livewire;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class CreateCommunityFeedback extends Component
{
    use WithFileUploads;

    public string $type = 'gop_y';

    public string $subject = '';

    public string $content = '';

    /** @var array<int, TemporaryUploadedFile> */
    public array $attachments = [];

    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
        $user = auth()->user();
        abort_unless($user instanceof User && $user->isCommunityParticipant(brand()->id), 403);
    }

    /** @return array<string, list<string>> */
    protected function rules(): array
    {
        return [
            'type' => ['required', 'in:gop_y,khieu_nai,bao_loi,thanh_toan,khac'],
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'min:10', 'max:5000'],
            'attachments' => ['array', 'max:3'],
            'attachments.*' => ['image', 'max:5120'],
        ];
    }

    public function removeAttachment(int $index): void
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    public function submit(): void
    {
        $this->validate();

        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        $paths = collect($this->attachments)
            ->map(fn (TemporaryUploadedFile $file) => $file->store('feedback/'.brand()->slug.'/'.$user->id, 'public'))
            ->filter(fn (mixed $path): bool => is_string($path))
            ->values()
            ->all();

        Feedback::create([
            'user_id' => $user->id,
            'brand_id' => brand()->id,
            'type' => $this->type,
            'subject' => trim($this->subject),
            'content' => trim($this->content),
            'attachments' => $paths ?: null,
        ]);

        $this->reset(['type', 'subject', 'content', 'attachments']);
        session()->flash('status', 'Đã gửi thành công. Đội ngũ DSCons sẽ xem và phản hồi cho bạn.');

        $this->redirect(community_route('feedbacks'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.create-community-feedback')
            ->layout('layouts.app', ['title' => 'Gửi góp ý & Khiếu nại · '.brand()->name]);
    }
}
