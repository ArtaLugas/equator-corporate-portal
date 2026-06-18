@props([
    'title' => '',
    'value' => '',
    'color' => 'primary',
    'trend' => null,
    'delta' => null,
    'sub' => null,
])

@php
    // Pemetaan warna latar belakang dan teks untuk ikon
    $colorStyles = [
        'primary' => 'bg-equator-dark/10 text-equator-dark',
        'bright'  => 'bg-equator-bright/10 text-equator-bright',
        'orange'  => 'bg-equator-orange/10 text-equator-orange',
        'success' => 'bg-emerald-100 text-emerald-700',
    ];
    $iconClass = $colorStyles[$color] ?? $colorStyles['primary'];
@endphp

<div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_2px_10px_-3px_rgba(38,53,146,0.05)] hover:shadow-[0_8px_30px_rgb(38,53,146,0.08)] hover:border-gray-200 transition-all duration-300 group flex flex-col h-full">

    {{-- Header: Ikon & Action --}}
    <div class="flex items-start justify-between mb-4">
        <div class="h-12 w-12 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110 {{ $iconClass }}">
            @if(isset($icon))
                {{ $icon }}
            @else
                {{-- Fallback Icon (Jika tidak ada slot ikon yang diberikan) --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/></svg>
            @endif
        </div>

        {{-- Tombol Options (Hover to reveal) --}}
        <button class="text-gray-400 hover:text-equator-dark opacity-0 group-hover:opacity-100 transition-opacity focus:outline-none p-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
        </button>
    </div>

    {{-- Content: Title & Value --}}
    <p class="text-xs font-bold text-gray-500 tracking-wide uppercase">{{ $title }}</p>
    <h3 class="text-3xl font-extrabold text-equator-text tracking-tight mt-1">
        {{ $value }}
    </h3>

    {{-- Footer: Trend & Subtext (Hanya dirender jika ada datanya) --}}
    @if($trend || $sub)
        <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-50">
            @if($trend && $delta)
                <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-0.5 rounded-md {{ $trend === 'up' ? 'text-emerald-700 bg-emerald-50' : 'text-rose-700 bg-rose-50' }}">
                    @if($trend === 'up')
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/><polyline points="16 17 22 17 22 11"/></svg>
                    @endif
                    {{ $delta }}
                </span>
            @endif

            @if($sub)
                <span class="text-xs font-semibold text-gray-400">{{ $sub }}</span>
            @endif
        </div>
    @endif
</div>
