<div class="card" style="padding:0.875rem;">
    <p class="widget-title">TRỤ CỘT</p>
    @if($burning)
    <div class="burning-indicator mb-2">
        <p style="font-size:0.7rem; font-weight:700; color:#991B1B;"> Vùng nóng</p>
        <p style="font-size:0.8rem; font-weight:600; color:#1A1A1A;">{{ $burning->pillar_label }} · +50% EXP</p>
    </div>
    @endif
    <div class="flex flex-col gap-1.5">
        @foreach(['offer'=>['label'=>'Offer','color'=>'#D97706'],'traffic'=>['label'=>'Thu hút','color'=>'#d17856'],'conversion'=>['label'=>'Chuyển đổi','color'=>'#059669'],'delivery'=>['label'=>'Cung ứng','color'=>'#2563EB'],'continuity'=>['label'=>'Continuity','color'=>'#DC2626']] as $key => $data)
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
