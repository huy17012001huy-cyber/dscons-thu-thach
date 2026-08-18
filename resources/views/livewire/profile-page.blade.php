<div>
    <div class="card mb-4" style="padding:1.5rem;">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="shrink-0" style="width:80px;">
                <div style="position:relative; width:80px; height:80px;">
                    <img src="{{ $profileUser->avatar_url }}" style="width:80px; height:80px; border-radius:50%; object-fit:cover; border:3px solid #E1E1E1;" alt="">
                    @if(auth()->id() === $profileUser->id)
                    <label style="position:absolute; bottom:0; right:0; background:#d17856; color:#FFF; width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; border:2px solid #FFF; z-index:2;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                        <input type="file" wire:model="avatarUpload" accept="image/*" style="display:none;">
                    </label>
                    @endif
                </div>
                @if(auth()->id() === $profileUser->id)
                <div wire:loading wire:target="avatarUpload" style="font-size:0.65rem; color:#d17856; text-align:center; margin-top:0.25rem;">Đang tải...</div>
                @endif
            </div>
            <div style="flex:1; min-width:0;">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    @if($editingProfile)
                    <div class="flex-1">
                        <input wire:model="editName" class="input" style="font-size:1rem; font-weight:700; margin-bottom:0.375rem;" placeholder="Tên hiển thị">
                        <input wire:model="editEmail" type="email" class="input" style="font-size:0.8rem; margin-bottom:0.375rem;" placeholder="Email">
                        <textarea wire:model="editBio" class="input" rows="2" placeholder="Giới thiệu ngắn (tùy chọn)" style="font-size:0.8rem; margin-bottom:0.375rem;"></textarea>
                        @error('editName') <p style="color:#991B1B; font-size:0.7rem;">{{ $message }}</p> @enderror
                        @error('editEmail') <p style="color:#991B1B; font-size:0.7rem;">{{ $message }}</p> @enderror
                        <div class="flex gap-2">
                            <button wire:click="saveProfile" wire:loading.attr="disabled" wire:target="saveProfile" class="btn btn-primary" style="font-size:0.75rem; padding:0.25rem 0.625rem;">
                                <span wire:loading.remove wire:target="saveProfile">Lưu</span>
                                <span wire:loading wire:target="saveProfile">Đang lưu...</span>
                            </button>
                            <button wire:click="cancelEditProfile" class="btn btn-ghost" style="font-size:0.75rem; padding:0.25rem 0.625rem;">Hủy</button>
                        </div>
                    </div>
                    @else
                    <h1 style="font-size:1.25rem; font-weight:800; color:#1A1A1A;">{{ $profileUser->name }}</h1>
                    @if(auth()->id() === $profileUser->id)
                    <button wire:click="startEditProfile" style="color:#5C5C66; cursor:pointer; padding:0.125rem;" title="Chỉnh sửa">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </button>
                    @endif
                    <span class="badge badge-class-{{ $profileUser->class_color }}" style="font-size:0.7rem;">{{ $profileUser->class_emoji }} {{ $profileUser->class_label }}</span>
                    <span class="level-badge" style="font-size:0.75rem;">Lv.{{ $profileUser->level }}</span>
                    @if($profileUser->da_count > 0)
                    <span class="da-gem" style="font-size:0.8rem;">◆ {{ $profileUser->da_count }} Đá</span>
                    @endif
                    @endif
                </div>
                @if(!$editingProfile)
                <p style="font-size:0.8rem; color:#5C5C66; margin-bottom:0.5rem;">{{ $profileUser->job_stage }}</p>
                @if($profileUser->bio)
                <p style="font-size:0.875rem; color:#2E2E2E; margin-bottom:0.75rem;">{{ $profileUser->bio }}</p>
                @endif
                @auth
                @if(auth()->id() !== $profileUser->id)
                <div class="flex gap-2 mb-2 flex-wrap">
                    <a href="{{ route('messages') }}?user={{ $profileUser->id }}" class="btn btn-ghost" style="font-size:0.75rem; padding:0.25rem 0.625rem;">
                        💬 Nhắn tin
                    </a>
                    @if(auth()->user()->is_admin && !$profileUser->is_admin && !session('impersonator_id'))
                    <form method="POST" action="{{ route('admin.impersonate.start', $profileUser->id) }}" onsubmit="return confirm('Đóng vai ' + {{ \Illuminate\Support\Js::from($profileUser->name) }} + '?')">
                        @csrf
                        <button type="submit" class="btn" style="font-size:0.75rem; padding:0.25rem 0.625rem; background:#7c2d12; color:#fff;" title="Xem giao diện như user này">
                            👁️ Đóng vai
                        </button>
                    </form>
                    @endif
                </div>
                @endif
                @endauth
                @endif

                <div class="flex justify-between mb-1">
                    <span style="font-size:0.7rem; color:#5C5C66;">{{ number_format($profileUser->xp) }} EXP</span>
                    <span style="font-size:0.7rem; color:#5C5C66;">Lv.{{ $profileUser->level + 1 }} → còn {{ number_format($toNext) }} EXP</span>
                </div>
                <div class="xp-bar mb-3" style="height:6px;">
                    <div class="xp-bar-fill" style="width:{{ $xpProgress }}%;"></div>
                </div>

                <div class="flex gap-4 flex-wrap">
                    <div><p style="font-size:1rem; font-weight:700; color:#d17856;">{{ number_format($profileUser->aip) }}</p><p style="font-size:0.65rem; color:#5C5C66;">AIP</p></div>
                    <div><p style="font-size:1rem; font-weight:700; color:#DC2626;">{{ $profileUser->streak }}</p><p style="font-size:0.65rem; color:#5C5C66;"> Streak</p></div>
                    <div><p style="font-size:1rem; font-weight:700; color:#1A1A1A;">{{ $profileUser->posts()->count() }}</p><p style="font-size:0.65rem; color:#5C5C66;">Bài viết</p></div>
                    <div><p style="font-size:1rem; font-weight:700; color:#d17856;">{{ $profileUser->posts()->where('is_cot',true)->count() }}</p><p style="font-size:0.65rem; color:#5C5C66;">★ CỐT</p></div>
                </div>
            </div>
        </div>

        {{-- Power Symbols --}}
        <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid #E1E1E1;">
            <p class="widget-title">POWER SYMBOLS</p>
            <div class="flex gap-3 flex-wrap">
                @foreach(['offer'=>'🔥','traffic'=>'✨','conversion'=>'🎯','delivery'=>'⚙️','continuity'=>'🔗'] as $p => $e)
                @php $sym = $symbols->get($p); $lv = $sym?->level ?? 0; @endphp
                <div class="text-center" style="opacity:{{ $lv > 0 ? '1' : '0.3' }};">
                    <div style="width:40px; height:40px; border-radius:50%; background:#E8F5E9; border:1px solid #d17856; display:flex; align-items:center; justify-content:center; font-size:1.125rem; margin:0 auto 0.25rem;">{{ $e }}</div>
                    <p style="font-size:0.65rem; color:{{ $lv > 0 ? '#1B5E20' : '#5C5C66' }}; font-weight:700;">{{ $lv > 0 ? 'Lv.'.$lv : '—' }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Badges --}}
        @if($badges->count() > 0)
        <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid #E1E1E1;">
            <p class="widget-title">HUY HIỆU</p>
            <div class="flex gap-2 flex-wrap">
                @foreach($badges as $ub)
                <div title="{{ $ub->badge->name }}: {{ $ub->badge->description }}" style="padding:0.25rem 0.5rem; border-radius:0.375rem; font-size:0.75rem; font-weight:600;
                    {{ match($ub->badge->rarity) {
                        'legendary' => 'background:#E8F5E9; color:#1B5E20; border:1px solid #d17856;',
                        'epic'      => 'background:#E8F5E9; color:#1B5E20; border:1px solid #81C784;',
                        'rare'      => 'background:#DBEAFE; color:#1E40AF; border:1px solid #93C5FD;',
                        default     => 'background:#F7F5F3; color:#5C5C66; border:1px solid #E1E1E1;',
                    } }}">
                    {{ $ub->badge->icon }} {{ $ub->badge->name }}
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Contribution Heatmap --}}
        <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid #E1E1E1;">
            @php
                $totalContributions = array_sum($contributions);
                $activeDays = count($contributions);
                $maxXp = max(1, max($contributions ?: [1]));
                $colors = ['#EEECE9', '#81C784', '#e7a07f', '#d17856', '#1B5E20'];

                // Build weeks array — exactly 53 columns, always ending on today's week
                $todayDate = now();
                $endOfWeek = $todayDate->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
                $startDate = $endOfWeek->copy()->subWeeks(52)->startOfWeek(\Carbon\Carbon::MONDAY);
                $weeks = [];
                $monthLabels = [];
                $cursor = $startDate->copy();
                $weekIdx = 0;

                while ($cursor->lte($endOfWeek)) {
                    $weekStart = $cursor->copy();
                    if ($cursor->day <= 7) {
                        $monthLabels[$weekIdx] = $cursor->locale('vi')->isoFormat('MMM');
                    }
                    $week = [];
                    for ($d = 0; $d < 7; $d++) {
                        $dayDate = $weekStart->copy()->addDays($d);
                        if ($dayDate->lt($startDate) || $dayDate->gt($endOfWeek)) {
                            $week[] = null; // outside 53-week range
                        } else {
                            $key = $dayDate->format('Y-m-d');
                            $week[] = ['date' => $key, 'xp' => $contributions[$key] ?? 0];
                        }
                    }
                    $weeks[] = $week;
                    $cursor->addWeek();
                    $weekIdx++;
                }
            @endphp

            <div class="flex items-center justify-between mb-2">
                <p class="widget-title" style="margin-bottom:0;">HOẠT ĐỘNG</p>
                <span style="font-size:0.65rem; color:#5C5C66;">{{ $activeDays }} ngày · {{ number_format($totalContributions) }} EXP năm qua</span>
            </div>

            <div style="overflow-x:auto;">
                {{-- Month labels --}}
                <div style="display:flex; gap:0; margin-left:28px; margin-bottom:2px;">
                    @foreach($weeks as $wi => $w)
                    <div style="width:13px; flex-shrink:0;">
                        @if(isset($monthLabels[$wi]))
                        <span style="font-size:0.55rem; color:#5C5C66; white-space:nowrap;">{{ $monthLabels[$wi] }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>

                <div style="display:flex; gap:0;">
                    {{-- Day labels --}}
                    <div style="display:flex; flex-direction:column; gap:2px; margin-right:4px; width:24px; flex-shrink:0;">
                        <div style="height:11px;"></div>
                        <div style="height:11px; display:flex; align-items:center;"><span style="font-size:0.55rem; color:#5C5C66;">T2</span></div>
                        <div style="height:11px;"></div>
                        <div style="height:11px; display:flex; align-items:center;"><span style="font-size:0.55rem; color:#5C5C66;">T4</span></div>
                        <div style="height:11px;"></div>
                        <div style="height:11px; display:flex; align-items:center;"><span style="font-size:0.55rem; color:#5C5C66;">T6</span></div>
                        <div style="height:11px;"></div>
                    </div>

                    {{-- Grid --}}
                    <div x-data="{ tip: '', tipX: 0, tipY: 0, showTip: false }" style="display:flex; gap:2px; position:relative;">
                        @foreach($weeks as $week)
                        <div style="display:flex; flex-direction:column; gap:2px;">
                            @foreach($week as $day)
                                @if($day === null)
                                <div style="width:11px; height:11px; border-radius:2px;"></div>
                                @else
                                @php
                                    $xp = $day['xp'];
                                    $level = 0;
                                    if ($xp > 0) {
                                        $pct = $xp / $maxXp;
                                        $level = match(true) {
                                            $pct >= 0.75 => 4,
                                            $pct >= 0.5  => 3,
                                            $pct >= 0.25 => 2,
                                            default      => 1,
                                        };
                                    }
                                    $dayLabel = \Carbon\Carbon::parse($day['date'])->locale('vi')->isoFormat('dd, D/M/Y');
                                    $tipText = $xp > 0 ? number_format($xp) . ' EXP · ' . $dayLabel : 'Nghỉ · ' . $dayLabel;
                                @endphp
                                <div @mouseenter="tip = '{{ addslashes($tipText) }}'; let r = $el.getBoundingClientRect(); tipX = r.left; tipY = r.top - 28; showTip = true"
                                     @mouseleave="showTip = false"
                                     style="width:11px; height:11px; border-radius:2px; background:{{ $colors[$level] }}; cursor:pointer;"></div>
                                @endif
                            @endforeach
                        </div>
                        @endforeach
                        {{-- Tooltip --}}
                        <div x-show="showTip" x-cloak class="heatmap-tip"
                             :style="'position:fixed; left:' + tipX + 'px; top:' + tipY + 'px; z-index:9999;'"
                             style="background:#1A1A1A; color:#FFF; padding:3px 8px; border-radius:4px; white-space:nowrap; pointer-events:none;">
                            <span x-text="tip"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-1 mt-1">
                <span style="font-size:0.6rem; color:#5C5C66;">Ít</span>
                @foreach($colors as $c)
                <div style="width:9px; height:9px; border-radius:2px; background:{{ $c }};"></div>
                @endforeach
                <span style="font-size:0.6rem; color:#5C5C66;">Nhiều</span>
            </div>
        </div>

        {{-- Affiliate link --}}
        @if(auth()->id() === $profileUser->id)
        <div style="margin-top:0.75rem; padding:0.625rem; background:#F7F5F3; border:1px solid #E1E1E1; border-radius:0.5rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
            <span style="font-size:0.75rem; color:#5C5C66;">Link mời của bạn:</span>
            <code style="font-size:0.75rem; color:#d17856; font-weight:600;">{{ url('/ref/'.$profileUser->username) }}</code>
        </div>
        @endif
    </div>

    <div class="tab-nav">
        <button wire:click="setTab('posts')" class="tab-item {{ $tab === 'posts' ? 'active' : '' }}">Bài viết</button>
        <button wire:click="setTab('cot')" class="tab-item {{ $tab === 'cot' ? 'active' : '' }}">★ CỐT</button>
        @if(auth()->id() === $profileUser->id)
        <button wire:click="setTab('bookmarks')" class="tab-item {{ $tab === 'bookmarks' ? 'active' : '' }}">🔖 Đã lưu</button>
        @endif
    </div>

    <div class="flex flex-col gap-3 mt-2">
        @if($tab === 'posts' && $posts)
            @forelse($posts as $post)
            <livewire:post-card :post="$post" :key="'prof-'.$post->id" />
            @empty
            <div class="card text-center py-8"><p style="color:#5C5C66;">Chưa có bài viết nào</p></div>
            @endforelse
            <div class="mt-4">{{ $posts->links() }}</div>
        @elseif($tab === 'cot' && $cotPosts)
            @forelse($cotPosts as $post)
            <livewire:post-card :post="$post" :key="'cot-prof-'.$post->id" />
            @empty
            <div class="card text-center py-8"><p style="color:#5C5C66;">Chưa có bài CỐT nào</p></div>
            @endforelse
            <div class="mt-4">{{ $cotPosts->links() }}</div>
        @elseif($tab === 'bookmarks' && $bookmarkedPosts)
            @forelse($bookmarkedPosts as $post)
            <livewire:post-card :post="$post" :key="'bm-'.$post->id" />
            @empty
            <div class="card text-center py-8"><p style="color:#5C5C66;">Chưa lưu bài viết nào</p></div>
            @endforelse
            <div class="mt-4">{{ $bookmarkedPosts->links() }}</div>
        @endif
    </div>
</div>
