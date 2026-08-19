<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ClassSelection extends Component
{
    public string $selectedClass = '';

    public array $classes = [
        'offer_architect' => [
            'emoji'       => '◆',
            'name'        => 'Offer Architect',
            'pillar'      => 'Offer',
            'description' => 'Thiết kế sản phẩm, định giá, xây value proposition',
            'bonus'       => '+20% XP khi review offer',
            'color'       => '#f59e0b',
            'bg'          => 'rgba(245,158,11,0.1)',
            'border'      => 'rgba(245,158,11,0.3)',
        ],
        'traffic_mage' => [
            'emoji'       => '◇',
            'name'        => 'Traffic Mage',
            'pillar'      => 'Thu hút',
            'description' => 'Content marketing, SEO, paid ads, viral growth',
            'bonus'       => 'Bài viết được ưu tiên +20% reach',
            'color'       => '#8b5cf6',
            'bg'          => 'rgba(139,92,246,0.1)',
            'border'      => 'rgba(139,92,246,0.3)',
        ],
        'conversion_ranger' => [
            'emoji'       => '▲',
            'name'        => 'Conversion Ranger',
            'pillar'      => 'Chuyển đổi',
            'description' => 'Copywriting, landing page, funnel, A/B test',
            'bonus'       => 'Review 5 sao → XP nhân đôi',
            'color'       => '#10b981',
            'bg'          => 'rgba(16,185,129,0.1)',
            'border'      => 'rgba(16,185,129,0.3)',
        ],
        'delivery_assassin' => [
            'emoji'       => '■',
            'name'        => 'Delivery Assassin',
            'pillar'      => 'Cung ứng',
            'description' => 'Vận hành, tự động hóa, hệ thống, fulfilment',
            'bonus'       => 'Hoàn thành Challenge đúng hạn → +30% XP',
            'color'       => '#3b82f6',
            'bg'          => 'rgba(59,130,246,0.1)',
            'border'      => 'rgba(59,130,246,0.3)',
        ],
        'continuity_captain' => [
            'emoji'       => '◎',
            'name'        => 'Continuity Captain',
            'pillar'      => 'Continuity',
            'description' => 'Retention, affiliate, xây cộng đồng, LTV',
            'bonus'       => 'Affiliate rate 25% + event 10 người → XP × 3',
            'color'       => '#ef4444',
            'bg'          => 'rgba(239,68,68,0.1)',
            'border'      => 'rgba(239,68,68,0.3)',
        ],
    ];

    public function selectClass(string $class): void
    {
        $this->selectedClass = $class;
    }

    public function confirm(): void
    {
        if (!$this->selectedClass || !array_key_exists($this->selectedClass, $this->classes)) {
            return;
        }

        $user = Auth::user();
        $user->update(['class' => $this->selectedClass]);

        $this->redirect(route('feed'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.class-selection')
            ->layout('layouts.guest', ['title' => 'Chọn Class — DSCons']);
    }
}
