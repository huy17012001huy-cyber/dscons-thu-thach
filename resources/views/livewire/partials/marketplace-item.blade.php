<article class="marketplace-item">
    @if($item->url)
        <a href="{{ $item->url }}" class="marketplace-item-cover" aria-label="Xem {{ $item->title }}">
    @else
        <div class="marketplace-item-cover">
    @endif
            @if($item->image)
                <img src="{{ $item->image }}" alt="">
            @else
                <span class="marketplace-item-fallback">{{ in_array($item->kind, ['resource', 'revit_tool'], true) ? 'DS' : strtoupper(substr($item->kind_label, 0, 2)) }}</span>
            @endif
            <span class="marketplace-kind"><x-icon name="{{ $item->kind === 'challenge' ? 'target' : ($item->kind === 'course' ? 'graduation' : ($item->kind === 'revit_tool' ? 'tool' : 'layers')) }}" size="13" />{{ $item->kind_label }}</span>
            @if($item->featured)<span class="marketplace-featured"><x-icon name="spark" size="13" />Nổi bật</span>@endif
    @if($item->url)</a>@else</div>@endif

    <div class="marketplace-item-body">
        <div class="marketplace-item-tags">
            @if($item->pillar)<span class="marketplace-tag is-blue"><x-icon name="{{ brand()->pillarProfiles()[$item->pillar]['icon'] ?? 'layers' }}" size="13" />{{ brand()->pillarLabel($item->pillar) }}</span>@endif
            @if($item->difficulty)
                @php($difficultyClass = $item->kind === 'challenge' ? match($item->difficulty) { 'Khó' => 'is-difficulty-hard', 'Hỗn loạn' => 'is-difficulty-chaos', default => 'is-difficulty-normal' } : '')
                <span class="marketplace-tag {{ $difficultyClass }}"><x-icon name="target" size="13" />{{ $item->kind === 'challenge' ? 'Độ khó: ' : '' }}{{ $item->difficulty }}</span>
            @endif
            @if($item->owned)<span class="marketplace-tag is-owned"><x-icon name="check-circle" size="13" />{{ in_array($item->kind, ['resource', 'revit_tool'], true) ? 'Đã mua' : 'Đã mở' }}</span>@elseif($item->pending)<span class="marketplace-tag is-pending"><x-icon name="clock" size="13" />Đang chờ thanh toán</span>@endif
        </div>
        <h3>{{ $item->title }}</h3>
        <p>{{ Str::limit($item->description ?: 'Nội dung được chọn lọc cho hành trình phát triển năng lực.', 96) }}</p>
        <div class="marketplace-item-meta"><span><x-icon name="clipboard" size="13" />{{ $item->meta }}</span>@if($item->member_count > 0)<span><x-icon name="users" size="13" />{{ number_format($item->member_count) }} người</span>@endif</div>

        <div class="marketplace-item-footer">
            <strong class="marketplace-price {{ $item->price <= 0 ? 'is-free' : '' }}"><x-icon name="tag" size="15" />{{ $item->price > 0 ? number_format($item->price, 0, ',', '.').'đ' : ($item->owned ? 'Đã sở hữu' : 'Miễn phí') }}</strong>
            @if($item->url)
                <a href="{{ $item->url }}" class="marketplace-cta">{{ $item->owned ? 'Mở nội dung' : 'Xem chi tiết' }} <x-icon name="arrow-right" size="15" /></a>
            @elseif($item->owned)
                @if($item->kind === 'revit_tool')
                    <a href="{{ route('account.revit-device') }}" class="marketplace-state is-owned" style="text-decoration:none"><x-icon name="check-circle" size="15" />Kích hoạt Revit</a>
                @else
                    <span class="marketplace-state is-owned"><x-icon name="check-circle" size="15" />Đã mua</span>
                @endif
            @elseif($item->pending)
                <span class="marketplace-state is-pending"><x-icon name="clock" size="15" />Chờ thanh toán</span>
            @else
                <button type="button" wire:click="purchase({{ $item->purchase_id }})" wire:loading.attr="disabled" wire:target="purchase({{ $item->purchase_id }})" class="marketplace-cta">{{ $item->price > 0 ? 'Mua ngay' : 'Nhận miễn phí' }} <x-icon name="arrow-right" size="15" /></button>
            @endif
        </div>
    </div>
</article>
