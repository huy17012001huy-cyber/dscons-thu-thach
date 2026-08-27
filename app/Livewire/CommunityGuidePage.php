<?php

namespace App\Livewire;

use App\Support\CommunityContentDefaults;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CommunityGuidePage extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
        $user = auth()->user();
        abort_unless($user instanceof User && $user->isCommunityParticipant(brand()->id), 403);
    }

    public function render(): View
    {
        $content = CommunityContentDefaults::resolve(brand()->guide_content, CommunityContentDefaults::guide());
        $user = auth()->user();
        $links = [
            'Bảng tin' => ['route' => 'feed', 'icon' => 'chat', 'tone' => 'blue'],
            'CỐT' => ['route' => 'cot', 'icon' => 'compass', 'tone' => 'orange'],
            'Tín hiệu' => ['route' => 'signals', 'icon' => 'flag', 'tone' => 'amber'],
            'Hỏi đáp kỹ thuật' => ['route' => 'qa', 'icon' => 'question', 'tone' => 'teal'],
            'Khóa học' => ['route' => 'academy', 'icon' => 'graduation', 'tone' => 'blue'],
            'Challenge' => ['route' => 'challenge', 'icon' => 'target', 'tone' => 'orange'],
            'Sự kiện' => ['route' => 'events', 'icon' => 'calendar', 'tone' => 'teal'],
            'Bảng xếp hạng' => ['route' => 'leaderboard', 'icon' => 'chart', 'tone' => 'blue'],
            'Marketplace' => ['route' => 'marketplace', 'icon' => 'shopping-cart', 'tone' => 'orange'],
            'Gói và đơn hàng' => ['route' => 'orders', 'icon' => 'receipt', 'tone' => 'blue'],
            'Affiliate' => ['route' => 'affiliate', 'icon' => 'user-plus', 'tone' => 'teal'],
            'CV của tôi' => ['route' => 'engineer.cv', 'icon' => 'cv', 'tone' => 'blue'],
            'Yêu cầu tuyển dụng' => ['route' => 'engineer.recruitment-requests', 'icon' => 'briefcase', 'tone' => 'orange'],
            'Góp ý và Khiếu nại' => ['route' => 'feedbacks', 'icon' => 'chat', 'tone' => 'teal'],
            'Tìm kiếm, thông báo và hồ sơ' => ['route' => 'search', 'icon' => 'search', 'tone' => 'blue'],
        ];

        if (! brand()->has_cv || ! ($user instanceof User && $user->isEngineer())) {
            unset($links['CV của tôi'], $links['Yêu cầu tuyển dụng']);
        }

        $sections = collect(CommunityContentDefaults::sections($content))
            ->map(function (array $section) use ($links): array {
                $link = $links[$section['title']] ?? null;
                if ($link) {
                    $link['url'] = community_route($link['route']);
                }

                $body = CommunityContentDefaults::normalizeUtf8($section['body']);

                return [
                    ...$section,
                    'link' => $link,
                    'summary' => preg_replace('/\s+/', ' ', $body) ?: '',
                    'details' => collect(preg_split('/\R/', trim($body)) ?: [])
                        ->map(function (string $line): array {
                            $parts = explode(':', trim($line), 2);

                            return [
                                'label' => trim($parts[0]),
                                'value' => trim($parts[1] ?? ''),
                            ];
                        })
                        ->filter(fn (array $detail) => $detail['label'] !== '')
                        ->values()
                        ->all(),
                ];
            })
            ->all();

        return view('livewire.community-guide-page', [
            'sections' => $sections,
        ])->layout('layouts.app', ['title' => 'Hướng dẫn sử dụng · '.brand()->name]);
    }
}
