@props([
    'noun' => 'item',
])

{{--
    Bulk "Move to Trash" action bar + styled confirmation modal.

    MUST be placed INSIDE the bulk <form x-data="{ selected: [] }"> that posts to
    the module's bulk-destroy route (the per-row checkboxes feed `selected`). The
    bar only appears once rows are selected; the red button opens a confirmation
    modal (mirrors <x-admin.confirm-delete>) instead of the native browser
    confirm(). The modal is teleported to <body> for safe positioning, and submits
    the surrounding form via the hidden submit button kept inside it.
--}}
<div x-data="{ confirmOpen: false, isSubmitting: false }">

    {{-- Action bar — visible only when at least one row is selected. --}}
    <div x-show="selected.length" x-cloak
        class="mb-3 flex items-center gap-3 rounded-xl bg-amber-50 px-4 py-2.5">
        <span class="text-sm font-semibold text-amber-800"><span x-text="selected.length"></span> selected</span>
        <button type="button" @click="confirmOpen = true"
            class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-bold text-white transition-colors hover:bg-rose-700 active:scale-[0.98]">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 6h18" />
                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                <path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2" />
            </svg>
            Move to Trash
        </button>
    </div>

    {{-- The real submit button — stays INSIDE the form; clicked by the modal. --}}
    <button type="submit" x-ref="doSubmit" class="hidden" tabindex="-1" aria-hidden="true"></button>

    {{-- Confirmation modal — teleported to <body> so overflow/stacking can't clip it. --}}
    <template x-teleport="body">
        <div x-show="confirmOpen" style="display: none;"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
            @keydown.escape.window="confirmOpen = false">

            {{-- Backdrop --}}
            <div x-show="confirmOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="confirmOpen = false"></div>

            {{-- Dialog --}}
            <div x-show="confirmOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4 sm:translate-y-0"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4 sm:translate-y-0"
                class="relative w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white"
                @click.stop>

                <div class="gap-4 px-6 py-6 sm:flex sm:items-start">
                    {{-- Warning badge --}}
                    <div
                        class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full border border-amber-100 bg-amber-50 sm:mx-0 sm:h-10 sm:w-10">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                            class="text-amber-600">
                            <path d="M3 6h18" />
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                            <path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2" />
                            <path d="M10 11v6" />
                            <path d="M14 11v6" />
                        </svg>
                    </div>

                    <div class="mt-3 text-center sm:ml-2 sm:mt-0 sm:text-left">
                        <h3 class="text-lg font-extrabold tracking-tight text-gray-900">Move to Trash</h3>
                        <div class="mt-2">
                            <p class="text-sm font-medium leading-relaxed text-gray-500">
                                Move <span class="font-bold text-gray-700" x-text="selected.length"></span>
                                selected {{ $noun }}(s) to trash? You can restore them from Trash later.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div
                    class="flex flex-col-reverse gap-3 border-t border-gray-100 bg-gray-50/50 px-6 py-4 sm:flex-row sm:justify-end">
                    <button @click="confirmOpen = false" type="button" :disabled="isSubmitting"
                        class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-bold text-gray-600 transition-colors hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-200 disabled:cursor-not-allowed disabled:opacity-50">
                        Cancel
                    </button>
                    <button type="button" @click="isSubmitting = true; $refs.doSubmit.click()" :disabled="isSubmitting"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500/50 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70 sm:w-auto">
                        <svg x-show="isSubmitting" class="h-4 w-4 animate-spin text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isSubmitting ? 'Moving...' : 'Move to Trash'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
