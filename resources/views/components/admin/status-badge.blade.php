@props([
    'variant' => null, // Pilihan: primary, secondary, success, info, warning, destructive, outline
    'status' => null, // Melempar string status DB langsung (active, draft, dll)
    'dot' => false, // Menampilkan titik indikator warna
    'size' => 'md',
])

@php
    use Illuminate\Support\Str;
    // 1. Jika pengguna melempar data $status, petakan ke $variant secara otomatis
    if ($status && !$variant) {
        $variant = match (strtolower($status)) {
            'active', 'published', 'completed', 'approved' => 'success',
            'inactive', 'draft', 'archived' => 'secondary',
            'ongoing', 'in_progress', 'processing' => 'info',
            'planned', 'pending', 'review' => 'warning',
            'failed', 'cancelled', 'error', 'rejected' => 'destructive',
            default => 'secondary',
        };
    }

    // Default variant jika tidak ada status dan tidak ada variant yang di-set
    $variant = $variant ?? 'primary';

    // 2. Base Classes: Tipografi presisi dan proporsi elemen (meniru Shadcn)
    $baseClasses =
        'inline-flex items-center gap-1.5 rounded-full border font-extrabold uppercase tracking-wider transition-colors';

    // 3. Variant Classes: Menggunakan palet Equator untuk menjaga Brand Identity
    $variantClasses = match ($variant) {
        'primary' => 'bg-equator-dark text-white border-transparent',
        'secondary' => 'bg-gray-100 border-gray-200 text-gray-600',
        'success' => 'bg-emerald-50 border-emerald-200 text-emerald-700',
        'info' => 'bg-equator-bright/10 border-equator-bright/20 text-equator-dark',
        'warning' => 'bg-orange-50 border-orange-200 text-orange-700',
        'destructive' => 'bg-red-50 border-red-200 text-red-700',
        'outline' => 'bg-transparent border-gray-300 text-gray-700',
        default => 'bg-gray-100 border-gray-200 text-gray-600',
    };

    // 4. Dot Colors: Menyesuaikan warna titik indikator dengan varian
    $dotClasses = match ($variant) {
        'primary' => 'bg-white',
        'secondary' => 'bg-gray-400',
        'success' => 'bg-emerald-500',
        'info' => 'bg-equator-bright',
        'warning' => 'bg-orange-500',
        'destructive' => 'bg-red-500',
        'outline' => 'bg-gray-400',
        default => 'bg-gray-400',
    };

    $sizeClasses = match ($size) {
        'sm' => 'px-2 py-0.5 text-[9px]',

        'md' => 'px-2.5 py-0.5 text-[10px]',

        'lg' => 'px-3 py-1 text-xs',

        default => 'px-2.5 py-0.5 text-[10px]',
    };
@endphp

<span {{ $attributes->merge(['class' => $baseClasses . ' ' . $sizeClasses . ' ' . $variantClasses]) }}>

    {{-- Indikator Titik (Opsional) --}}
    @if ($dot)
        <span class="{{ $dotClasses }} h-1.5 w-1.5 shrink-0 rounded-full"></span>
    @endif

    {{-- Jika $status ada, cetak status. Jika tidak, cetak apapun yang ada di dalam tag (Slot) --}}
    {{ $status ? Str::headline($status) : $slot }}

</span>
