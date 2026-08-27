<?php

namespace App\Livewire;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Modules\Community\Application\CommunityTopicData;
use Modules\Community\Application\CommunityTopicService;

class AdminTopics extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    #[Rule('required|max:60')]
    public string $name = '';

    #[Rule('nullable|max:10')]
    public string $emoji = '';

    #[Rule('required|max:80')]
    public string $slug = '';

    #[Rule('required|integer|min:0')]
    public int $sort_order = 0;

    public bool $is_active = true;

    public function updatedName(string $value): void
    {
        if (! $this->editingId) {
            $this->slug = Str::slug($value);
        }
    }

    public function openCreate(): void
    {
        if (! $this->currentUser()?->isCommunityAdmin()) {
            return;
        }

        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        if (! $this->currentUser()?->isCommunityAdmin()) {
            return;
        }

        $topic = Topic::query()->findOrFail($id);
        $this->editingId = $topic->id;
        $this->name = $topic->name;
        $this->emoji = $topic->emoji ?? '';
        $this->slug = $topic->slug;
        $this->sort_order = $topic->sort_order;
        $this->is_active = $topic->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $user = $this->currentUser();
        if (! $user?->isCommunityAdmin()) {
            return;
        }
        $this->validate();

        try {
            app(CommunityTopicService::class)->save($this->editingId, $user, new CommunityTopicData(
                name: trim($this->name),
                emoji: trim($this->emoji) ?: null,
                slug: trim($this->slug),
                sortOrder: $this->sort_order,
                isActive: $this->is_active,
            ));
        } catch (InvalidArgumentException $exception) {
            $this->addError('slug', $exception->getMessage());

            return;
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $user = $this->currentUser();
        if ($user?->isCommunityAdmin()) {
            app(CommunityTopicService::class)->toggleActive($id, $user);
        }
    }

    public function delete(int $id): void
    {
        $user = $this->currentUser();
        if ($user?->isCommunityAdmin()) {
            app(CommunityTopicService::class)->delete($id, $user);
        }
    }

    public function render(): View
    {
        return view('livewire.admin-topics', [
            'topics' => Topic::query()->orderBy('sort_order')->orderBy('name')->get(),
        ])->layout('layouts.app');
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'emoji', 'slug', 'sort_order', 'editingId']);
        $this->is_active = true;
    }

    private function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }
}
