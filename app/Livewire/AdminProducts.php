<?php

namespace App\Livewire;

use App\Models\DigitalProduct;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Modules\Commerce\Application\ProductCatalogManagementService;

class AdminProducts extends Component
{
    use WithFileUploads;

    public bool $showForm = false;

    public ?int $editingId = null;

    #[Rule('required|min:3|max:200')]
    public string $title = '';

    public string $description = '';

    public string $pillar = '';

    public int|float $price = 0;

    public string $deliveryType = 'file';

    public string $accessUrl = '';

    public ?TemporaryUploadedFile $uploadFile = null;

    public bool $isPublished = true;

    public bool $isFeatured = false;

    public string $productKind = 'resource';

    public string $toolKey = '';

    public string $supportedRevitVersions = '';

    public string $toolManifestVersion = '1.0.0';

    public bool $isLicenseRequired = false;

    public function create(): void
    {
        if (! $this->currentUser()?->isBrandAdmin()) {
            return;
        }

        $this->reset(['editingId', 'title', 'description', 'pillar', 'price', 'deliveryType', 'accessUrl', 'uploadFile', 'isPublished', 'isFeatured', 'productKind', 'toolKey', 'supportedRevitVersions', 'toolManifestVersion', 'isLicenseRequired']);
        $this->isPublished = true;
        $this->isFeatured = false;
        $this->productKind = 'resource';
        $this->supportedRevitVersions = '';
        $this->toolManifestVersion = '1.0.0';
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        if (! $this->currentUser()?->isBrandAdmin()) {
            return;
        }

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
        $this->productKind = $product->product_kind ?: 'resource';
        $this->toolKey = $product->tool_key ?? '';
        $this->supportedRevitVersions = implode(', ', $product->supported_revit_versions ?: []);
        $this->toolManifestVersion = $product->tool_manifest_version ?: '1.0.0';
        $this->isLicenseRequired = (bool) $product->is_license_required;
        $this->showForm = true;
    }

    public function save(): void
    {
        $actor = $this->currentUser();
        if (! $actor?->isBrandAdmin()) {
            return;
        }
        $this->validate();
        if ($this->productKind === 'revit_tool') {
            $this->validate([
                'toolKey' => ['required', 'regex:/^[a-z0-9-]+$/', 'max:80'],
                'toolManifestVersion' => ['required', 'max:32'],
            ]);
        }
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
            'product_kind' => $this->productKind,
            'tool_key' => $this->productKind === 'revit_tool' ? $this->toolKey : null,
            'supported_revit_versions' => $this->productKind === 'revit_tool' ? collect(explode(',', $this->supportedRevitVersions))->map(fn ($version) => trim($version))->filter()->values()->all() : null,
            'tool_manifest_version' => $this->productKind === 'revit_tool' ? $this->toolManifestVersion : null,
            'is_license_required' => $this->productKind === 'revit_tool' && $this->isLicenseRequired,
        ];

        // Handle file upload
        if ($this->uploadFile) {
            $path = $this->uploadFile->store('products', 'public');
            if (is_string($path)) {
                $data['file_path'] = $path;
                $data['file_name'] = $this->uploadFile->getClientOriginalName();
            }
        }

        $product = app(ProductCatalogManagementService::class)->save(
            $this->editingId,
            $actor,
            $data,
        );
        if (! $product) {
            return;
        }

        if ($this->editingId) {
            $this->dispatch('toast', message: 'Đã cập nhật sản phẩm!', type: 'success');
        } else {
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
        $actor = $this->currentUser();
        if (! $actor?->isBrandAdmin()) {
            return;
        }
        app(ProductCatalogManagementService::class)->togglePublished($id, $actor);
    }

    public function deleteProduct(int $id): void
    {
        $actor = $this->currentUser();
        if (! $actor?->isBrandAdmin()) {
            return;
        }
        $product = app(ProductCatalogManagementService::class)->delete($id, $actor);
        if (! $product) {
            return;
        }
        if ($product->file_path) {
            Storage::disk('public')->delete($product->file_path);
        }
        $this->dispatch('toast', message: 'Đã xóa sản phẩm', type: 'success');
    }

    private function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    public function render(): View
    {
        $products = DigitalProduct::withCount('purchases')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.admin-products', ['products' => $products])
            ->layout('layouts.app', ['title' => 'Quản lý sản phẩm — Admin']);
    }
}
