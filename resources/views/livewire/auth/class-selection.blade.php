<div>
    <div style="max-width:680px; margin:0 auto;">
        <div class="text-center mb-8">
            <h1 style="font-size:1.75rem; font-weight:800; color:#1A1A1A; margin-bottom:0.5rem;">Chọn Class của bạn</h1>
            <p style="color:#5C5C66; font-size:0.9rem;">Class phản ánh chuyên môn cốt lõi. <strong style="color:#d17856;">Không đổi được</strong> (trừ khi dùng AIP).</p>
        </div>

        <div class="grid grid-cols-1 gap-3 mb-6" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
            @foreach($classes as $key => $class)
            <button
                wire:click="selectClass('{{ $key }}')"
                style="
                    background: {{ $selectedClass === $key ? $class['bg'] : '#FFFFFF' }};
                    border: 2px solid {{ $selectedClass === $key ? $class['color'] : '#E1E1E1' }};
                    border-radius: 0.75rem;
                    padding: 1.25rem;
                    text-align: left;
                    cursor: pointer;
                    transition: all 0.2s;
                    position: relative;
                    box-shadow: {{ $selectedClass === $key ? '0 2px 8px rgba(0,0,0,0.08)' : 'none' }};
                ">
                @if($selectedClass === $key)
                <span style="position:absolute; top:0.75rem; right:0.75rem; width:20px; height:20px; background:{{ $class['color'] }}; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                    <svg width="12" height="12" fill="white" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </span>
                @endif

                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.75rem;">
                    <span style="font-size:2rem; line-height:1;">{{ $class['emoji'] }}</span>
                    <div>
                        <p style="font-weight:700; color:#1A1A1A; font-size:0.95rem;">{{ $class['name'] }}</p>
                        <span class="badge" style="background:{{ $class['bg'] }}; color:{{ $class['color'] }}; border:1px solid {{ $class['border'] }}; font-size:0.65rem;">
                            {{ $class['pillar'] }}
                        </span>
                    </div>
                </div>

                <p style="color:#5C5C66; font-size:0.8rem; margin-bottom:0.5rem; line-height:1.4;">{{ $class['description'] }}</p>

                <div style="display:flex; align-items:center; gap:0.375rem; padding:0.375rem 0.625rem; background:#E8F5E9; border:1px solid #d17856; border-radius:0.375rem;">
                    <svg width="12" height="12" fill="#d17856" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <span style="font-size:0.7rem; color:#1B5E20; font-weight:600;">{{ $class['bonus'] }}</span>
                </div>
            </button>
            @endforeach
        </div>

        <div class="text-center">
            <button
                wire:click="confirm"
                class="btn btn-primary"
                style="padding:0.75rem 2.5rem; font-size:1rem; {{ !$selectedClass ? 'opacity:0.4; cursor:not-allowed;' : '' }}"
                @if(!$selectedClass) disabled @endif>
                Xác nhận — Tôi là {{ $selectedClass ? $classes[$selectedClass]['name'] : '...' }}
            </button>
            <p style="color:#5C5C66; font-size:0.75rem; margin-top:0.75rem;">! Chọn cẩn thận — chỉ đổi được 1 lần/năm với 1,000 AIP</p>
            @if(auth()->user()->level < 10)
            <a href="{{ route('feed') }}" style="display:inline-block; margin-top:0.75rem; font-size:0.8rem; color:#5C5C66;">Bỏ qua — chọn sau khi đạt Lv.10 →</a>
            @endif
        </div>
    </div>
</div>
