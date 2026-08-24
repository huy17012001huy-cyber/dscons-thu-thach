<div class="card sidebar-widget" style="padding:0.875rem;">
    <p class="widget-title">PHÂN BỐ CHUYÊN MÔN · {{ $total }} thành viên</p>
    @foreach($classes as $key => $data)
    @php $r = $ratios[$key] ?? ['count'=>0,'pct'=>0]; @endphp
    <div class="flex items-center gap-2 mb-2">
        <span style="width:1.25rem;display:grid;place-items:center;"><x-icon name="{{ $data['icon'] }}" size="15" color="{{ $data['color'] }}" /></span>
        <div style="flex:1; min-width:0;">
            <div class="flex justify-between mb-0.5">
                <span style="font-size:0.7rem; color:#2E2E2E; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $data['name'] }}</span>
                <span style="font-size:0.7rem; color:#5C5C66; white-space:nowrap;">{{ $r['pct'] }}%</span>
            </div>
            <div class="progress-bar" style="height:3px;">
                <div style="width:{{ $r['pct'] }}%; height:100%; border-radius:9999px; background:{{ $data['color'] }};"></div>
            </div>
        </div>
    </div>
    @endforeach
</div>
