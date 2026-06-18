{{-- Props: $title, $subtitle (optional), $breadcrumb (optional array of [label]) --}}
<section class="relative overflow-hidden bg-equator-dark py-16 text-white sm:py-20">
    <div class="absolute inset-0 bg-gradient-to-b from-equator-darker/40 to-equator-dark"></div>
    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <nav class="mb-3 flex items-center gap-2 text-xs font-medium text-white/60">
            <a href="{{ route('home') }}" class="hover:text-white">Home</a>
            <span>/</span>
            <span class="text-white/90">{{ $title }}</span>
        </nav>
        <h1 class="font-heading text-3xl font-semibold tracking-tight sm:text-4xl lg:text-5xl">{{ $title }}</h1>
        @isset($subtitle)
            <p class="mt-4 max-w-2xl text-base leading-relaxed text-white/80">{{ $subtitle }}</p>
        @endisset
    </div>
</section>
