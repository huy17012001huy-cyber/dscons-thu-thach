<div class="relative notification-bell" x-data="{ open: false }" @click.away="open = false">
    <button @click="open = !open; if(open) $wire.toggleDropdown()"
        aria-label="Thông báo" :aria-expanded="open.toString()" aria-haspopup="true"
        style="position:relative; padding:0.375rem; border-radius:0.375rem; color:#5C5C66; transition:color 0.15s;" class="hover:text-gray-900">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        @if($count > 0)
        <span aria-hidden="true" style="position:absolute; top:-2px; right:-2px; background:#EF4444; color:white; font-size:0.7rem; font-weight:700; min-width:16px; height:16px; padding:0 4px; border-radius:8px; display:flex; align-items:center; justify-content:center; line-height:1;">{{ $count > 9 ? '9+' : $count }}</span>
        <span class="sr-only">{{ $count }} thông báo chưa đọc</span>
        @endif
    </button>

    @if($showDropdown)
    <div class="notification-dropdown" style="position:absolute; right:0; top:calc(100% + 8px); background:#FFFFFF; border:1px solid #E1E1E1; border-radius:0.75rem; width:320px; max-height:400px; overflow-y:auto; z-index:100; box-shadow:0 8px 32px rgba(0,0,0,0.10);">
        <div class="flex items-center justify-between" style="padding:0.75rem 1rem; border-bottom:1px solid #E1E1E1;">
            <p style="font-weight:600; font-size:0.875rem; color:#1A1A1A;">Thông báo</p>
            @if($count > 0)
            <button wire:click="markAllRead" wire:loading.attr="disabled" wire:target="markAllRead" style="font-size:0.7rem; color:#d17856; font-weight:600; cursor:pointer;">
                <span wire:loading.remove wire:target="markAllRead">Đánh dấu đã đọc</span>
                <span wire:loading wire:target="markAllRead">Đang đánh dấu...</span>
            </button>
            @endif
        </div>
        <div style="padding:0.25rem;">
            @forelse($notifications as $n)
            <button wire:click="openNotification('{{ $n->id }}')" style="display:block; width:100%; text-align:left; padding:0.625rem 0.75rem; border-radius:0.5rem; margin-bottom:2px; cursor:pointer; {{ is_null($n->read_at) ? 'background:#FFFBEB;' : '' }}">
                <div class="flex items-start gap-2">
                    <span style="font-size:1rem; flex-shrink:0;">{{ $n->data['icon'] ?? '●' }}</span>
                    <div style="flex:1; min-width:0;">
                        <p style="font-size:0.8rem; color:#1A1A1A; line-height:1.4;">{{ $n->data['message'] ?? '—' }}</p>
                        <p style="font-size:0.65rem; color:#5C5C66; margin-top:0.25rem;">{{ $n->created_at->diffForHumans() }}</p>
                    </div>
                    @if(is_null($n->read_at))
                    <span style="width:6px; height:6px; border-radius:50%; background:#d17856; flex-shrink:0; margin-top:0.375rem;"></span>
                    @endif
                </div>
            </button>
            @empty
            <p style="text-align:center; color:#5C5C66; font-size:0.8rem; padding:1.5rem;">Không có thông báo nào</p>
            @endforelse
        </div>
    </div>
    @endif
</div>
