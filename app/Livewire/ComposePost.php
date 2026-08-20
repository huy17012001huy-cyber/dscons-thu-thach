<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\PostImage;
use App\Models\Topic;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ComposePost extends Component
{
    use WithFileUploads;

    public bool $expanded = false;
    public int $dailyPostLimit = 5;
    public array $uploadedImages = [];

    #[Rule('nullable|max:150')]
    public string $title = '';

    #[Rule('required|min:5|max:50000')]
    public string $content = '';

    #[Rule('required|in:offer,traffic,conversion,delivery,continuity')]
    public string $pillar = '';

    public bool $isSignal = false;

    #[Rule('nullable|exists:topics,id')]
    public ?int $topic_id = null;

    public array $pillars = [
        'offer' => ['emoji' => '🔥', 'label' => 'Offer'],
        'traffic' => ['emoji' => '✨', 'label' => 'Traffic'],
        'conversion' => ['emoji' => '🎯', 'label' => 'Conversion'],
        'delivery' => ['emoji' => '⚙️', 'label' => 'Delivery'],
        'continuity' => ['emoji' => '🔗', 'label' => 'Continuity'],
    ];

    public array $imageUploads = [];

    /**
     * Keep validation copy useful in the UI instead of exposing Laravel's
     * translation keys when the project does not ship a language pack yet.
     */
    protected array $messages = [
        'title.max' => 'Tiêu đề tối đa :max ký tự.',
        'content.required' => 'Vui lòng nhập nội dung bài viết.',
        'content.min' => 'Nội dung cần ít nhất :min ký tự.',
        'content.max' => 'Nội dung tối đa :max ký tự.',
        'pillar.required' => 'Vui lòng chọn một Pillar trước khi đăng bài.',
        'pillar.in' => 'Pillar đã chọn không hợp lệ.',
        'topic_id.exists' => 'Chủ đề đã chọn không tồn tại.',
        'imageUploads.*.image' => 'Tệp đính kèm phải là hình ảnh.',
        'imageUploads.*.max' => 'Mỗi hình ảnh tối đa :max KB.',
    ];

    public function updatedImageUploads(): void
    {
        $this->validate(['imageUploads.*' => 'image|max:5120']);

        foreach ($this->imageUploads as $image) {
            if (count($this->uploadedImages) >= 4) {
                break;
            }

            $this->uploadedImages[] = $image->store('post-images', 'public');
        }

        $this->imageUploads = [];
    }

    public function open(): void
    {
        $this->expanded = true;
        $this->resetValidation();
    }

    public function cancel(): void
    {
        $this->deleteUploadedImages();
        $this->resetForm();
    }

    public function removeImage(int $index): void
    {
        if (!isset($this->uploadedImages[$index])) {
            return;
        }

        Storage::disk('public')->delete($this->uploadedImages[$index]);
        array_splice($this->uploadedImages, $index, 1);
    }

    public function submit(): void
    {
        if (!Auth::check()) {
            return;
        }

        $this->validate();

        $user = Auth::user();
        $dayKey = Carbon::now('Asia/Ho_Chi_Minh')->format('Y-m-d');
        $lock = Cache::lock('compose-post-day:'.$user->id.':'.$dayKey, 10);
        if (!$lock->get()) {
            $this->addError('content', 'Bài viết đang được xử lý. Vui lòng thử lại sau một chút.');
            return;
        }

        try {
            $postsToday = $this->todayPostCount($user->id);

        if ($postsToday >= $this->dailyPostLimit) {
            $this->addError('content', 'Bạn đã đạt giới hạn '.$this->dailyPostLimit.' bài hôm nay. Bạn có thể đăng lại sau 00:00 (giờ Việt Nam).');
            return;
        }

        if ($this->isSignal && str_word_count($this->content) > 500) {
            $this->addError('content', 'Tín hiệu tối đa 500 từ.');
            return;
        }

        $post = Post::create([
            'user_id' => $user->id,
            'title' => $this->title ?: null,
            'content' => $this->content,
            'pillar' => $this->pillar,
            'topic_id' => $this->topic_id ?: null,
            'is_signal' => $this->isSignal,
        ]);

        foreach ($this->uploadedImages as $order => $path) {
            PostImage::create([
                'post_id' => $post->id,
                'path' => $path,
                'order_index' => $order,
            ]);
        }
        } finally {
            $lock->release();
        }

        $this->resetForm();
        $this->dispatch('post-created');
        $this->dispatch('toast', message: 'Đã đăng bài viết.', type: 'success');
    }

    private function todayPostCount(int $userId): int
    {
        $timezone = 'Asia/Ho_Chi_Minh';
        $start = Carbon::now($timezone)->startOfDay()->utc();
        $end = Carbon::now($timezone)->endOfDay()->utc();

        return Post::withTrashed()
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    private function deleteUploadedImages(): void
    {
        foreach ($this->uploadedImages as $path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function resetForm(): void
    {
        $this->reset(['title', 'content', 'pillar', 'topic_id', 'isSignal', 'expanded', 'uploadedImages']);
        $this->imageUploads = [];
        $this->resetValidation();
    }

    public function render()
    {
        $postsToday = Auth::check() ? $this->todayPostCount(Auth::id()) : 0;

        return view('livewire.compose-post', [
            'topics' => Topic::active()->get(),
            'postsToday' => $postsToday,
            'remainingPosts' => max(0, $this->dailyPostLimit - $postsToday),
        ]);
    }
}
