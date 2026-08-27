<?php

namespace App\Livewire;

use App\Models\CommunityPostType;
use App\Models\CommunitySubject;
use App\Models\Topic;
use App\Models\User;
use App\Support\PostContentRenderer;
use App\Support\PostHtmlSanitizer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Modules\Community\Application\PostPublishData;
use Modules\Community\Application\PostPublishingService;

class ComposePost extends Component
{
    use WithFileUploads;

    public bool $expanded = false;

    public string $editorMode = 'write';

    public string $contentHtml = '';

    public int $dailyPostLimit = 5;

    /** @var list<string> */
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

    /** @var array<string, array{icon: string, label: string}> */
    public array $pillars = [
        'offer' => ['icon' => 'layers', 'label' => 'Offer'],
        'traffic' => ['icon' => 'bolt', 'label' => 'Traffic'],
        'conversion' => ['icon' => 'target', 'label' => 'Conversion'],
        'delivery' => ['icon' => 'settings', 'label' => 'Delivery'],
        'continuity' => ['icon' => 'users', 'label' => 'Continuity'],
    ];

    /** @var list<TemporaryUploadedFile> */
    public array $imageUploads = [];

    /** @var array<string, string> */
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

    public function mount(): void
    {
        $this->pillars = collect(brand()->pillarProfiles())
            ->map(fn (array $pillar) => ['icon' => $pillar['icon'], 'label' => $pillar['name']])
            ->all();
    }

    public function updatedImageUploads(): void
    {
        $this->validate(['imageUploads.*' => 'image|max:5120']);

        foreach ($this->imageUploads as $image) {
            if (count($this->uploadedImages) >= 4) {
                break;
            }

            $path = $image->store('post-images', 'public');
            if (is_string($path)) {
                $this->uploadedImages[] = $path;
            }
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
        if (! isset($this->uploadedImages[$index])) {
            return;
        }

        Storage::disk('public')->delete($this->uploadedImages[$index]);
        array_splice($this->uploadedImages, $index, 1);
    }

    public function submit(): void
    {
        $user = $this->authenticatedUser();
        if (! $user) {
            $this->addError('content', 'Bạn cần tham gia cộng đồng trước khi đăng bài.');

            return;
        }

        $this->prepareContentForValidation();
        $this->syncLegacyPillar();
        $this->validate();

        $outcome = app(PostPublishingService::class)->publish($user, new PostPublishData(
            content: $this->content,
            pillar: $this->pillar,
            title: $this->title ?: null,
            contentHtml: $this->contentHtml ?: null,
            topicId: $this->topic_id,
            subjectId: $this->subject_id,
            postTypeId: $this->post_type_id,
            imagePaths: $this->uploadedImages,
        ), $this->dailyPostLimit);
        if ($outcome->error) {
            $this->addError('content', $outcome->error);

            return;
        }

        $this->resetForm();
        $this->dispatch('post-created');
        $this->dispatch('toast', message: 'Đã đăng bài viết.', type: 'success');
    }

    public function previewContent(): string
    {
        return filled($this->contentHtml)
            ? app(PostHtmlSanitizer::class)->sanitize($this->contentHtml)
            : app(PostContentRenderer::class)->render($this->content);
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

    private function authenticatedUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    public function render(): View
    {
        $user = $this->authenticatedUser();
        $postsToday = $user ? app(PostPublishingService::class)->postsToday($user) : 0;

        return view('livewire.compose-post', [
            'topics' => Topic::active()->get(),
            'subjects' => CommunitySubject::active()->where('slug', '!=', 'tieu-chuan')->get(),
            'postTypes' => CommunityPostType::active()->get(),
            'postsToday' => $postsToday,
            'remainingPosts' => max(0, $this->dailyPostLimit - $postsToday),
        ]);
    }
}
