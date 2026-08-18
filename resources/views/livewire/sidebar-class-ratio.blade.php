<div class="card" style="padding:0.875rem;">
    <p class="widget-title">PHÂN BỐ CLASS · {{ $total }} member</p>
    @foreach([
        'offer_architect'    => ['emoji'=>'🔥','label'=>'Offer Architect','color'=>'#D97706'],
        'traffic_mage'       => ['emoji'=>'✨','label'=>'Traffic Mage','color'=>'#d17856'],
        'conversion_ranger'  => ['emoji'=>'🎯','label'=>'Conversion Ranger','color'=>'#059669'],
        'delivery_assassin'  => ['emoji'=>'⚙️','label'=>'Delivery Assassin','color'=>'#2563EB'],
        'continuity_captain' => ['emoji'=>'🔗','label'=>'Continuity Captain','color'=>'#DC2626'],
    ] as $key => $data)
    @php $r = $ratios[$key] ?? ['count'=>0,'pct'=>0]; @endphp
    <div class="flex items-center gap-2 mb-2">
        <span style="font-size:0.875rem; width:1.25rem; text-align:center;">{{ $data['emoji'] }}</span>
        <div style="flex:1; min-width:0;">
            <div class="flex justify-between mb-0.5">
                <span style="font-size:0.7rem; color:#2E2E2E; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $data['label'] }}</span>
                <span style="font-size:0.7rem; color:#5C5C66; white-space:nowrap;">{{ $r['pct'] }}%</span>
            </div>
            <div class="progress-bar" style="height:3px;">
                <div style="width:{{ $r['pct'] }}%; height:100%; border-radius:9999px; background:{{ $data['color'] }};"></div>
            </div>
        </div>
    </div>
    @endforeach
</div>
