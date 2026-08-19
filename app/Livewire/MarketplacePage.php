<?php

namespace App\Livewire;

use App\Models\DigitalProduct;
use App\Models\ProductPurchase;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MarketplacePage extends Component
{
    public string $pillar = '';

    public function setPillar(string $p): void
    {
        $this->pillar = $this->pillar === $p ? '' : $p;
    }

    public function purchase(int $productId): void
    {
        if (!Auth::check()) return;
        $user = Auth::user();
        $product = DigitalProduct::where('is_published', true)->findOrFail($productId);

        // Already purchased?
        $existing = ProductPurchase::where('user_id', $user->id)
            ->where('digital_product_id', $product->id)
            ->first();
        if ($existing) {
            $msg = $existing->status === 'active' ? 'Bạn đã mua sản phẩm này rồi!' : 'Đang chờ thanh toán!';
            $this->dispatch('toast', message: $msg, type: 'info');
            return;
        }

        if ($product->isFree()) {
            ProductPurchase::create([
                'user_id' => $user->id,
                'digital_product_id' => $product->id,
                'status' => 'active',
                'paid_at' => now(),
            ]);
            $this->dispatch('toast', message: 'Nhận sản phẩm thành công!', type: 'success');
        } else {
            ProductPurchase::create([
                'user_id' => $user->id,
                'digital_product_id' => $product->id,
                'status' => 'pending_payment',
            ]);
            $this->dispatch('toast', message: 'Vui lòng chuyển khoản để nhận sản phẩm!', type: 'info');
        }
    }

    public function render()
    {
        $user = Auth::user();
        $query = DigitalProduct::where('is_published', true);

        if ($this->pillar) {
            $query->where('pillar', $this->pillar);
        }

        $products = $query->orderBy('sort_order')->orderByDesc('created_at')->get();

        // User's purchases
        $purchasedIds = [];
        $pendingIds = [];
        if ($user) {
            $purchases = ProductPurchase::where('user_id', $user->id)->get();
            $purchasedIds = $purchases->where('status', 'active')->pluck('digital_product_id')->toArray();
            $pendingIds = $purchases->where('status', 'pending_payment')->pluck('digital_product_id')->toArray();
        }

        return view('livewire.marketplace-page', [
            'products' => $products,
            'purchasedIds' => $purchasedIds,
            'pendingIds' => $pendingIds,
        ])->layout('layouts.app', ['title' => 'Marketplace — DSCons']);
    }
}
