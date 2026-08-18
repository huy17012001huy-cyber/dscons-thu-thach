<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\PostImage;
use App\Models\Topic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ComposePost extends Component
{
    use WithFileUploads;

    public bool $expanded = false;

    public array $uploadedImages = [];

    #[Rule('nullable|max:150')]
    public string $title = '';

    #[Rule('required|min:5|max:50000')]
    public string $content = '';

    #[Rule('nullable|in:offer,traffic,conversion,delivery,continuity')]
    public string $pillar = '';

    public bool $isSignal = false;

    #[Rule('nullable|exists:topics,id')]
    public ?int $topic_id = null;

    public array $pillars = [
        'offer' => ['emoji' => '🔥', 'label' => 'Offer'],
        'traffic' => ['emoji' => '✨', 'label' => 'Thu hút'],
        'conversion' => ['emoji' => '🎯', 'label' => 'Chuyển đổi'],
        'delivery' => ['emoji' => '⚙️', 'label' => 'Cung ứng'],
        'continuity' => ['emoji' => '🔗', 'label' => 'Continuity'],
    ];

    public $imageUploads = [];

    public function updatedImageUploads(): void
    {
        $this->validate(['imageUploads.*' => 'image|max:5120']); // 5MB per image
        foreach ($this->imageUploads as $img) {
            if (count($this->uploadedImages) >= 4) {
                break;
            } // Max 4 images
            $path = $img->store('post-images', 'public');
            $this->uploadedImages[] = $path;
        }
        $this->imageUploads = [];
    }

    public function removeImage(int $index): void
    {
        if (isset($this->uploadedImages[$index])) {
            Storage::disk('public')->delete($this->uploadedImages[$index]);
            array_splice($this->uploadedImages, $index, 1);
        }
    }

    public function submit(): void
    {
        $this->validate();

        $user = Auth::user();

        $throttleKey = 'compose-post:'.$user->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('content', 'Bạn đang đăng bài quá nhanh. Vui lòng thử lại sau '.round($seconds / 60).' phút.');

            return;
        }

        if ($user->level < 10) {
            $this->addError('content', 'Bạn cần đạt Level 10 để đăng bài. Hãy tương tác bằng comment để lên level!');

            return;
        }

        if ($this->isSignal && str_word_count($this->content) > 500) {
            $this->addError('content', 'Tín hiệu tối đa 500 từ');

            return;
        }

        $post = Post::create([
            'user_id' => $user->id,
            'title' => $this->title ?: null,
            'content' => $this->content,
            'pillar' => $this->pillar ?: null,
            'topic_id' => $this->topic_id ?: null,
            'is_signal' => $this->isSignal,
        ]);

        // Save uploaded images
        foreach ($this->uploadedImages as $i => $path) {
            PostImage::create(['post_id' => $post->id, 'path' => $path, 'order_index' => $i]);
        }

        RateLimiter::hit($throttleKey, 3600); // 5 posts per hour

        $this->reset(['title', 'content', 'pillar', 'topic_id', 'isSignal', 'expanded', 'uploadedImages']);
        $this->dispatch('post-created');
    }

    public function render()
    {
        return view('livewire.compose-post', [
            'topics' => Topic::active()->get(),
        ]);
    }
}
