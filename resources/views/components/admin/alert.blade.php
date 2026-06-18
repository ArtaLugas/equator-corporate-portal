{{-- SUCCESS ALERT --}}
@if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
        class="mb-6 flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div
                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
            </div>
            <p class="text-sm font-bold text-emerald-800">
                {{ session('success') }}
            </p>
        </div>

        {{-- Manual Close Button --}}
        <button @click="show = false"
            class="text-emerald-500 transition-colors hover:text-emerald-700 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6 6 18" />
                <path d="m6 6 12 12" />
            </svg>
        </button>
    </div>
@endif

{{-- ERROR ALERT --}}
@if (session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
        class="mb-6 flex items-center justify-between rounded-xl border border-rose-200 bg-rose-50 p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
            </div>
            <p class="text-sm font-bold text-rose-800">
                {{ session('error') }}
            </p>
        </div>

        {{-- Manual Close Button --}}
        <button @click="show = false" class="text-rose-500 transition-colors hover:text-rose-700 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6 6 18" />
                <path d="m6 6 12 12" />
            </svg>
        </button>
    </div>
@endif
