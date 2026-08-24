<div class="card sidebar-widget" style="padding:0.875rem;">
    @php $pillars = brand()->pillarProfiles(); @endphp
    <p class="widget-title">TRỤ CỘT</p>
    @if($burning)
    <div class="burning-indicator mb-2">
        <p style="font-size:0.7rem; font-weight:700; color:#991B1B;"> Vùng nóng</p>
        <p style="font-size:0.8rem; font-weight:600; color:#1A1A1A;">{{ $burning->pillar_label }} · +50% EXP</p>
    </div>
    @endif
    <div class="flex flex-col gap-1.5">
        @foreach($pillars as $key => $pillarData)
        @php $data = ['label' => $pillarData['name'], 'color' => $pillarData['color']]; @endphp
        @php $stat = $stats->get($key); $pct = $stat?->post_pct ?? 20; @endphp
        <div>
            <div class="flex justify-between mb-0.5">
                <span style="font-size:0.7rem; color:#2E2E2E;">{{ $data['label'] }}</span>
                <span style="font-size:0.7rem; color:#5C5C66;">{{ number_format($pct, 0) }}%</span>
            </div>
            <div class="progress-bar" style="height:4px;">
                <div class="progress-fill" style="width:{{ $pct }}%; background:{{ $data['color'] }};"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>
