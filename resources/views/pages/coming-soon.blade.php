<x-layouts.app :title="$page . ' — DSCons'">
    <div class="card text-center" style="max-width:680px;margin:0 auto;padding:clamp(2rem,7vw,4rem) 1.5rem;">
        <div class="status-icon" style="margin:0 auto 1.25rem;" aria-hidden="true">
            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v3m0 12v3M3 12h3m12 0h3m-4.64-6.36l-2.12 2.12m-8.48 8.48l-2.12 2.12m0-12.72l2.12 2.12m8.48 8.48l2.12 2.12"/><circle cx="12" cy="12" r="3.5"/></svg>
        </div>
        <h1 style="font-size:clamp(1.3rem,3vw,1.7rem);font-weight:800;color:var(--ds-text);margin-bottom:.55rem;">{{ $page }}</h1>
        <p style="font-size:.9rem;color:var(--ds-text-muted);">Tính năng này đang được phát triển · Phase 2</p>
    </div>
</x-layouts.app>
