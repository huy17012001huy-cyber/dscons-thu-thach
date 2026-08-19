<div class="card mb-4">

    {{-- COLLAPSED --}}
    @if(!$expanded)
    <div wire:click="$set('expanded', true)"
        class="flex gap-3 items-center" style="cursor:text;">
        <img src="{{ auth()->user()->avatar_url }}" class="avatar w-9 h-9 shrink-0" alt="">
        <div style="flex:1; background:#F7F5F3; border:1px solid #E1E1E1; border-radius:0.5rem; padding:0.625rem 0.875rem; color:#5C5C66; font-size:0.9rem; line-height:1.4;">
            Chia sẻ điều gì đó hôm nay…
            <span style="color:#d17856; font-weight:600; font-size:0.78rem; margin-left:0.25rem;">Viết bài</span>
        </div>
    </div>

    {{-- EXPANDED --}}
    @else
    <div>
        {{-- Header --}}
        <div class="flex items-center gap-2.5 pb-3" style="border-bottom:1px solid #EEECE9;">
            <img src="{{ auth()->user()->avatar_url }}" class="avatar w-9 h-9 shrink-0" alt="">
            <p style="font-size:0.875rem; color:#5C5C66; line-height:1.3;">
                <span style="font-weight:600; color:#1A1A1A;">{{ auth()->user()->name }}</span>
                &nbsp;đang đăng trong&nbsp;
                <span style="font-weight:700; color:#1A1A1A;">DSCons</span>
            </p>
        </div>

        {{-- Title input --}}
        <input
            wire:model="title"
            type="text"
            placeholder="Tiêu đề (tùy chọn)"
            style="width:100%; border:none; outline:none; padding:0.875rem 0 0.25rem; font-size:1.1rem; font-weight:700; color:#1A1A1A; background:transparent; font-family:inherit;"
        >
        @error('title')
            <p style="color:#991B1B; font-size:0.75rem; margin-bottom:0.25rem;">{{ $message }}</p>
        @enderror

        {{-- Image previews --}}
        @if(count($uploadedImages) > 0)
        <div class="flex gap-2 flex-wrap py-2">
            @foreach($uploadedImages as $i => $img)
            <div style="position:relative; width:80px; height:80px; border-radius:0.5rem; overflow:hidden; border:1px solid #E1E1E1;">
                <img src="{{ asset('storage/' . $img) }}" alt="" style="width:100%; height:100%; object-fit:cover;">
                <button wire:click="removeImage({{ $i }})" style="position:absolute; top:2px; right:2px; background:rgba(0,0,0,0.6); color:#FFF; border-radius:50%; width:18px; height:18px; font-size:0.6rem; display:flex; align-items:center; justify-content:center; cursor:pointer;">✕</button>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Content textarea --}}
        <textarea
            wire:model="content"
            x-data
            x-init="$el.style.height = $el.scrollHeight + 'px'"
            @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
            placeholder="Chia sẻ kiến thức, case study, hoặc insight của bạn..."
            style="width:100%; border:none; outline:none; padding:0.375rem 0 0.875rem; font-size:0.9375rem; color:#1A1A1A; background:transparent; resize:none; overflow:hidden; font-family:inherit; line-height:1.65; min-height:120px;"
            autofocus></textarea>
        @error('content')
            <p style="color:#991B1B; font-size:0.75rem; margin-bottom:0.5rem;">{{ $message }}</p>
        @enderror

        {{-- Footer toolbar — 1 hàng duy nhất --}}
        @php
            $selectedTopic = $topics->firstWhere('id', $topic_id);
            $ib = 'display:inline-flex;align-items:center;justify-content:center;width:1.75rem;height:1.75rem;border-radius:0.375rem;color:#A8A8B0;cursor:pointer;flex-shrink:0;transition:color 0.1s,background 0.1s;';
            $ibHover = "onmouseover=\"this.style.color='#1A1A1A';this.style.background='#F0EEE9'\" onmouseout=\"this.style.color='#A8A8B0';this.style.background='transparent'\"";
        @endphp
        <div class="flex items-center justify-between pt-2" style="gap:0;">

            {{-- Left: icon buttons --}}
            <div class="flex items-center" style="gap:0; flex-shrink:0;">
                <label title="Đính kèm ảnh (tối đa 4)" aria-label="Đính kèm ảnh (tối đa 4)" style="{{ $ib }} cursor:pointer;" {!! $ibHover !!}>
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
                    <input type="file" wire:model="imageUploads" multiple accept="image/*" style="display:none;">
                </label>
                <div wire:loading wire:target="imageUploads" style="font-size:0.65rem; color:#d17856;">Đang tải...</div>
                <button type="button" title="Chèn link" aria-label="Chèn link" style="{{ $ib }}" {!! $ibHover !!}>
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                </button>
                <button type="button" title="Chèn video" aria-label="Chèn video" style="{{ $ib }}" {!! $ibHover !!}>
                    <svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                </button>
                <button type="button" title="Tạo poll" aria-label="Tạo poll" style="{{ $ib }}" {!! $ibHover !!}>
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
                </button>
                <button type="button" title="Emoji" aria-label="Chọn emoji" style="{{ $ib }}" {!! $ibHover !!}>
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 13s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                </button>
                <button type="button" title="GIF" aria-label="Chèn GIF" style="{{ $ib }} font-size:0.63rem; font-weight:800;" {!! $ibHover !!}>GIF</button>
                <div style="width:1px; height:1.1rem; background:#E1E1E1; margin:0 6px; flex-shrink:0;"></div>
                <button wire:click="$toggle('isSignal')" title="Tín hiệu ngắn <500 từ" aria-label="Chuyển sang bài Tín hiệu ngắn" aria-pressed="{{ $isSignal ? 'true' : 'false' }}"
                    style="{{ $ib }} {{ $isSignal ? 'color:#065F46 !important;background:#D1FAE5 !important;' : '' }}"
                    onmouseover="this.style.background='{{ $isSignal ? '#A7F3D0' : '#F0EEE9' }}'"
                    onmouseout="this.style.background='{{ $isSignal ? '#D1FAE5' : 'transparent' }}'">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
                </button>
            </div>

            {{-- Right: dropdowns + actions --}}
            <div class="flex items-center" style="gap:6px; flex-shrink:0;">

                {{-- Pillar dropdown --}}
                <div x-data="{ open: false }" style="position:relative;">
                    @if($pillar)
                    <button @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="listbox" class="badge badge-pillar-{{ $pillar }}"
                        style="cursor:pointer; padding:0.2rem 0.55rem; font-size:0.75rem; white-space:nowrap;">
                        {{ $pillars[$pillar]['emoji'] }} {{ $pillars[$pillar]['label'] }} ▾
                    </button>
                    @else
                    <button @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="listbox"
                        style="display:inline-flex;align-items:center;padding:0.2rem 0.55rem;border-radius:0.375rem;font-size:0.75rem;font-weight:500;cursor:pointer;background:#F7F5F3;color:#5C5C66;border:1px solid #E1E1E1;white-space:nowrap;">
                        Trụ cột ▾
                    </button>
                    @endif
                    @error('pillar') <span style="position:absolute;top:calc(100% + 2px);right:0;color:#991B1B;font-size:0.7rem;white-space:nowrap;">{{ $message }}</span> @enderror
                    <div x-show="open" @click.away="open = false" x-transition
                        style="position:absolute;right:0;top:calc(100% + 6px);background:#FFFFFF;border:1px solid #E1E1E1;border-radius:0.625rem;padding:0.25rem;min-width:160px;z-index:50;box-shadow:0 4px 20px rgba(0,0,0,0.09);">
                        @foreach($pillars as $key => $data)
                        <button wire:click="$set('pillar', '{{ $key }}')" @click="open = false"
                            class="badge badge-pillar-{{ $key }}"
                            style="display:flex;width:100%;padding:0.5rem 0.75rem;border-radius:0.375rem;font-size:0.8rem;cursor:pointer;margin-bottom:1px;{{ $pillar === $key ? 'outline:2px solid currentColor;outline-offset:-1px;' : '' }}">
                            {{ $data['emoji'] }} {{ $data['label'] }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Topic dropdown --}}
                @if($topics->count())
                <div x-data="{ open: false }" style="position:relative;">
                    <button @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="listbox"
                        style="display:inline-flex;align-items:center;padding:0.2rem 0.55rem;border-radius:0.375rem;font-size:0.75rem;font-weight:600;cursor:pointer;border:1px solid;white-space:nowrap;
                            {{ $selectedTopic ? 'background:#F0FDF4;color:#1B5E20;border-color:#d17856;' : 'background:#F7F5F3;color:#5C5C66;border-color:#E1E1E1;' }}">
                        {{ $selectedTopic ? $selectedTopic->emoji.' '.$selectedTopic->name : 'Chủ đề' }} ▾
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition
                        style="position:absolute;right:0;top:calc(100% + 6px);background:#FFFFFF;border:1px solid #E1E1E1;border-radius:0.625rem;padding:0.25rem;min-width:180px;z-index:50;box-shadow:0 4px 20px rgba(0,0,0,0.09);">
                        @if($topic_id)
                        <button wire:click="$set('topic_id', null)" @click="open = false"
                            style="display:flex;width:100%;padding:0.45rem 0.75rem;border-radius:0.375rem;font-size:0.8rem;cursor:pointer;color:#5C5C66;gap:0.5rem;margin-bottom:2px;">✕ Bỏ chọn</button>
                        <div style="height:1px;background:#EEECE9;margin:0.25rem 0;"></div>
                        @endif
                        @foreach($topics as $t)
                        <button wire:click="$set('topic_id', {{ $t->id }})" @click="open = false"
                            style="display:flex;align-items:center;gap:0.5rem;width:100%;padding:0.5rem 0.75rem;border-radius:0.375rem;font-size:0.8rem;cursor:pointer;margin-bottom:1px;
                                {{ $topic_id == $t->id ? 'background:#F0FDF4;color:#1B5E20;font-weight:600;' : 'color:#1A1A1A;' }}">
                            <span style="width:1.25rem;text-align:center;">{{ $t->emoji ?? '📌' }}</span>
                            {{ $t->name }}
                            @if($topic_id == $t->id)
                            <svg style="margin-left:auto;" width="13" height="13" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            @endif
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif

                <div style="width:1px;height:1.1rem;background:#E1E1E1;flex-shrink:0;"></div>
                <button wire:click="$set('expanded', false)" class="btn btn-ghost"
                    style="font-size:0.78rem;padding:0.25rem 0.6rem;">Hủy</button>
                <button wire:click="submit" wire:loading.attr="disabled" wire:target="submit" class="btn btn-primary"
                    style="font-size:0.8rem;padding:0.28rem 0.875rem;white-space:nowrap;">
                    <span wire:loading.remove wire:target="submit">Đăng bài</span>
                    <span wire:loading wire:target="submit">Đang đăng...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
