@props([
    'action',
    'title' => 'Delete Data',
    'message' => 'Are you sure you want to delete this data? This action cannot be undone.',
])

<div x-data="{ open: false }">

    {{-- TRIGGER BUTTON (Subtle Flat Design untuk Tabel) --}}
    {{-- Awalnya abu-abu transparan, baru menyala merah saat di-hover --}}
    <button @click="open = true" type="button"
        class="group inline-flex h-8 w-8 items-center justify-center rounded-lg bg-rose-500 text-white transition-colors"
        title="Delete">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
            class="transition-transform group-active:scale-90">
            <path d="M3 6h18" />
            <path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2" />
            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
            <path d="M10 11v6" />
            <path d="M14 11v6" />
        </svg>
    </button>

    {{-- MODAL (Teleported to Body) --}}
    <template x-teleport="body">
        <div x-show="open" style="display: none;"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
            @keydown.escape.window="open = false">
            {{-- Layer 1: Backdrop Blur --}}
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="open = false"></div>

            {{-- Layer 2: Modal Container (FLAT DESIGN - NO SHADOW) --}}
            <div x-show="open" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4 sm:translate-y-0"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4 sm:translate-y-0"
                class="relative w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white"
                @click.stop>
                {{-- BODY --}}
                <div class="gap-4 px-6 py-6 sm:flex sm:items-start">

                    {{-- Warning Icon (Premium Flat Badge) --}}
                    <div
                        class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full border border-rose-100 bg-rose-50 sm:mx-0 sm:h-10 sm:w-10">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                            stroke-linejoin="round" class="text-rose-600">
                            <path d="M3 6h18" />
                            <path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2" />
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                            <path d="M10 11v6" />
                            <path d="M14 11v6" />
                        </svg>
                    </div>

                    {{-- Text Content --}}
                    <div class="mt-3 text-center sm:ml-2 sm:mt-0 sm:text-left">
                        <h3 class="text-lg font-extrabold tracking-tight text-gray-900">
                            {{ $title }}
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm font-medium leading-relaxed text-gray-500">
                                {{ $message }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- FOOTER (Actions) --}}
                <div x-data="{ isSubmitting: false }"
                    class="flex flex-col-reverse gap-3 border-t border-gray-100 bg-gray-50/50 px-6 py-4 sm:flex-row sm:justify-end">
                    {{-- Cancel Button (Flat) --}}
                    <button @click="open = false" type="button" :disabled="isSubmitting"
                        class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-bold text-gray-600 transition-colors hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-200 disabled:cursor-not-allowed disabled:opacity-50">
                        Cancel
                    </button>

                    {{-- Delete Form (Flat) --}}
                    <form action="{{ $action }}" method="POST" class="m-0" @submit="isSubmitting = true">
                        @csrf
                        @method('DELETE')

                        <button type="submit" :disabled="isSubmitting"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500/50 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70 sm:w-auto">
                            {{-- SVG Loading Spinner --}}
                            <svg x-show="isSubmitting" class="h-4 w-4 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>

                            <span x-text="isSubmitting ? 'Deleting...' : 'Delete Data'"></span>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </template>
</div>
