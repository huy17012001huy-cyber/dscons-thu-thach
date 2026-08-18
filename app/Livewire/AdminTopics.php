<?php

namespace App\Livewire;

use App\Models\Topic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Component;

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
        if (!$this->editingId) {
            $this->slug = Str::slug($value);
        }
    }

    public function openCreate(): void
    {
        $this->reset(['name', 'emoji', 'slug', 'sort_order', 'editingId']);
        $this->is_active = true;
        $this->showForm  = true;
    }

    public function openEdit(int $id): void
    {
        $topic            = Topic::findOrFail($id);
        $this->editingId  = $id;
        $this->name       = $topic->name;
        $this->emoji      = $topic->emoji ?? '';
        $this->slug       = $topic->slug;
        $this->sort_order = $topic->sort_order;
        $this->is_active  = $topic->is_active;
        $this->showForm   = true;
    }

    public function save(): void
    {
        if (!Auth::user()?->is_admin) return;
        $this->validate();

        $data = [
            'name'       => $this->name,
            'emoji'      => $this->emoji ?: null,
            'slug'       => $this->slug,
            'sort_order' => $this->sort_order,
            'is_active'  => $this->is_active,
        ];

        if ($this->editingId) {
            Topic::findOrFail($this->editingId)->update($data);
        } else {
            Topic::create($data);
        }

        $this->showForm = false;
        $this->reset(['name', 'emoji', 'slug', 'sort_order', 'editingId']);
        $this->is_active = true;
    }

    public function toggleActive(int $id): void
    {
        if (!Auth::user()?->is_admin) return;
        Topic::findOrFail($id)->update(['is_active' => !Topic::findOrFail($id)->is_active]);
    }

    public function delete(int $id): void
    {
        if (!Auth::user()?->is_admin) return;
        Topic::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.admin-topics', [
            'topics' => Topic::orderBy('sort_order')->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
