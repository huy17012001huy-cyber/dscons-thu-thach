<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\PostImage;
use App\Models\CommunityPostType;
use App\Models\CommunitySubject;
use App\Models\Topic;
use App\Support\PostHtmlSanitizer;
use App\Support\PostContentRenderer;
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
    public string $editorMode = 'write';
    public string $contentHtml = '';
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

    #[Rule('nullable|exists:community_subjects,id')]
    public ?int $subject_id = null;

    #[Rule('nullable|exists:community_post_types,id')]
    public ?int $post_type_id = null;

    public array $pillars = [
        'offer' => ['icon' => 'layers', 'label' => 'Offer'],
        'traffic' => ['icon' => 'bolt', 'label' => 'Traffic'],
        'conversion' => ['icon' => 'target', 'label' => 'Conversion'],
        'delivery' => ['icon' => 'settings', 'label' => 'Delivery'],
        'continuity' => ['icon' => 'users', 'label' => 'Continuity'],
    ];

    public array $imageUploads = [];

    public function mount(): void
    {
        $this->pillars = collect(brand()->pillarProfiles())
            ->map(fn (array $pillar) => ['icon' => $pillar['icon'], 'label' => $pillar['name']])
            ->all();
    }

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
        if (!Auth::check() || !Auth::user()->isCommunityParticipant()) {
            $this->addError('content', 'Bạn cần tham gia cộng đồng trước khi đăng bài.');
            return;
        }

        $this->prepareContentForValidation();
        $this->syncLegacyPillar();
        $this->validate();
        $this->validateCommunityTaxonomy();

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

        $post = Post::create([
            'user_id' => $user->id,
            'brand_id' => brand()->id,
            'title' => $this->title ?: null,
            'content' => $this->content,
            'content_html' => filled($this->contentHtml)
                ? app(PostHtmlSanitizer::class)->sanitize($this->contentHtml)
                : null,
            'content_format' => filled($this->contentHtml) ? 'html' : 'markdown',
            'pillar' => $this->pillar,
            'topic_id' => $this->topic_id ?: null,
            'subject_id' => $this->subject_id ?: null,
            'post_type_id' => $this->post_type_id ?: null,
            'is_signal' => false,
        ]);

        $post->update(['slug' => $this->buildSlug($post)]);

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

    public function previewContent(): string
    {
        if (filled($this->contentHtml)) {
            return app(PostHtmlSanitizer::class)->sanitize($this->contentHtml);
        }

        return app(PostContentRenderer::class)->render($this->content);
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
        $this->reset(['title', 'content', 'contentHtml', 'pillar', 'topic_id', 'subject_id', 'post_type_id', 'isSignal', 'expanded', 'editorMode', 'uploadedImages']);
        $this->imageUploads = [];
        $this->resetValidation();
    }

    private function prepareContentForValidation(): void
    {
        if (! filled($this->contentHtml)) {
            return;
        }

        $this->contentHtml = app(PostHtmlSanitizer::class)->sanitize($this->contentHtml);
        $this->content = trim(strip_tags($this->contentHtml));
    }

    private function syncLegacyPillar(): void
    {
        if (! $this->subject_id) {
            return;
        }

        $slug = CommunitySubject::query()->whereKey($this->subject_id)->value('slug');
        $this->pillar = match ($slug) {
            'thiet-ke' => 'traffic',
            'dung-hinh', 'phoi-hop-combine' => 'offer',
            'boc-tach' => 'delivery',
            'family', 'meo-hay' => 'conversion',
            'tieu-chuan' => 'continuity',
            default => $this->pillar ?: 'offer',
        };
    }

    private function validateCommunityTaxonomy(): void
    {
        if ($this->subject_id && ! CommunitySubject::query()->whereKey($this->subject_id)->exists()) {
            $this->addError('subject_id', 'Chủ đề không thuộc cộng đồng hiện tại.');
        }

        if ($this->post_type_id && ! CommunityPostType::query()->whereKey($this->post_type_id)->exists()) {
            $this->addError('post_type_id', 'Loại nội dung không thuộc cộng đồng hiện tại.');
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages($this->getErrorBag()->toArray());
        }
    }

    private function buildSlug(Post $post): string
    {
        $base = \Illuminate\Support\Str::slug($post->title ?: \Illuminate\Support\Str::limit($this->content, 60, '')) ?: 'bai-viet';
        return $base.'-'.$post->id;
    }

    public function render()
    {
        $postsToday = Auth::check() ? $this->todayPostCount(Auth::id()) : 0;

        return view('livewire.compose-post', [
            'topics' => Topic::active()->get(),
            'subjects' => CommunitySubject::active()
                ->where('slug', '!=', 'tieu-chuan')
                ->get(),
            'postTypes' => CommunityPostType::active()->get(),
            'postsToday' => $postsToday,
            'remainingPosts' => max(0, $this->dailyPostLimit - $postsToday),
        ]);
    }
}
