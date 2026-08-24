<?php

namespace App\Livewire;

use App\Models\DigitalProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class AdminProducts extends Component
{
    use WithFileUploads;

    public bool $showForm = false;
    public ?int $editingId = null;

    #[Rule('required|min:3|max:200')]
    public string $title = '';
    public string $description = '';
    public string $pillar = '';
    public int $price = 0;
    public string $deliveryType = 'file';
    public string $accessUrl = '';
    public $uploadFile = null;
    public bool $isPublished = true;
    public bool $isFeatured = false;

    public function create(): void
    {
        $this->reset(['editingId', 'title', 'description', 'pillar', 'price', 'deliveryType', 'accessUrl', 'uploadFile', 'isPublished', 'isFeatured']);
        $this->isPublished = true;
        $this->isFeatured = false;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $product = DigitalProduct::findOrFail($id);
        $this->editingId = $id;
        $this->title = $product->title;
        $this->description = $product->description ?? '';
        $this->pillar = $product->pillar ?? '';
        $this->price = $product->price;
        $this->deliveryType = $product->delivery_type;
        $this->accessUrl = $product->access_url ?? '';
        $this->isPublished = $product->is_published;
        $this->isFeatured = (bool) $product->is_featured;
        $this->showForm = true;
    }

    public function save(): void
    {
        if (!Auth::user()?->is_admin) return;
        $this->validate();
        if ($this->uploadFile) {
            $this->validate(['uploadFile' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf,zip,doc,docx,xlsx,mp4|max:51200']);
        }

        $data = [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'pillar' => $this->pillar ?: null,
            'price' => $this->price,
            'delivery_type' => $this->deliveryType,
            'access_url' => $this->accessUrl ?: null,
            'is_published' => $this->isPublished,
            'is_featured' => $this->isFeatured,
        ];

        // Handle file upload
        if ($this->uploadFile) {
            $path = $this->uploadFile->store('products', 'public');
            $data['file_path'] = $path;
            $data['file_name'] = $this->uploadFile->getClientOriginalName();
        }

        if ($this->editingId) {
            DigitalProduct::where('id', $this->editingId)->update($data);
            $this->dispatch('toast', message: 'Đã cập nhật sản phẩm!', type: 'success');
        } else {
            DigitalProduct::create($data);
            $this->dispatch('toast', message: 'Đã tạo sản phẩm!', type: 'success');
        }

        $this->showForm = false;
    }

    public function cancel(): void
    {
        $this->showForm = false;
    }

    public function togglePublish(int $id): void
    {
        if (!Auth::user()?->is_admin) return;
        $product = DigitalProduct::findOrFail($id);
        $product->update(['is_published' => !$product->is_published]);
    }

    public function deleteProduct(int $id): void
    {
        if (!Auth::user()?->is_admin) return;
        $product = DigitalProduct::findOrFail($id);
        if ($product->file_path) {
            Storage::disk('public')->delete($product->file_path);
        }
        $product->delete();
        $this->dispatch('toast', message: 'Đã xóa sản phẩm', type: 'success');
    }

    public function render()
    {
        $products = DigitalProduct::withCount('purchases')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.admin-products', ['products' => $products])
            ->layout('layouts.app', ['title' => 'Quản lý sản phẩm — Admin']);
    }
}
