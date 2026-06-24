@props([
    'groups',          // Collection<ServiceCategory> with loaded ->services
    'active' => null,  // Service|null currently selected
])

@php
    use Illuminate\Support\Str;

    // Short tab label + one-line description, mapped from the existing category name.
    // (Static copy lives here in the component — NOT a new DB field.)
    $catMeta = function ($name) {
        $n = Str::lower($name);
        return match (true) {
            str_contains($n, 'assessment') || str_contains($n, 'planning')   => [__('projects.capability_assessment_label'), __('projects.capability_assessment_desc')],
            str_contains($n, 'implementation') || str_contains($n, 'assistance') => [__('projects.capability_implementation_label'), __('projects.capability_implementation_desc')],
            str_contains($n, 'monitoring') || str_contains($n, 'evaluation')  => [__('projects.capability_monitoring_label'), __('projects.capability_monitoring_desc')],
            default => [$name, ''],
        };
    };

    $base = request()->except(['service', 'page']);

    $capabilityData = $groups->map(function ($cat) use ($catMeta, $base, $active) {
        [$label, $desc] = $catMeta($cat->name);
        return [
            'label' => $label,
            'desc' => $desc,
            'count' => $cat->services->count(),
            'services' => $cat->services->map(fn ($s) => [
                'name' => $s->name,
                'href' => route('projects.index', array_merge($base, ['service' => $s->slug])),
                'active' => $active && $active->id === $s->id,
            ])->values(),
        ];
    })->values();

    // Open on the category that holds the active service (else the first).
    $activeTabIndex = 0;
    if ($active) {
        foreach ($groups->values() as $i => $cat) {
            if ($cat->services->contains('id', $active->id)) { $activeTabIndex = $i; break; }
        }
    }

    $clearHref = route('projects.index', $base);
@endphp

@if ($groups->isNotEmpty())
    <div x-data="capabilityFilter()" class="relative" @keydown.escape.window="close()">

        {{-- ── Trigger ── --}}
        <div class="inline-flex items-stretch">
            <button type="button" @click="open ? close() : openPanel()"
                @class([
                    'inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium transition-colors',
                    'rounded-r-none border-r-0' => (bool) $active,
                    'border-equator-dark bg-equator-dark text-white' => (bool) $active,
                    'border-slate-200 bg-slate-50 text-equator-text hover:bg-slate-100' => ! $active,
                ])>
                <i class="bi bi-grid-1x2 text-xs {{ $active ? 'text-equator-orange' : 'text-slate-400' }}"></i>
                <span class="max-w-[11rem] truncate">{{ $active?->name ?? __('projects.capability_trigger') }}</span>
                <i class="bi bi-chevron-down text-[0.6rem] transition-transform" :class="open && 'rotate-180'"></i>
            </button>
            @if ($active)
                <a href="{{ $clearHref }}" aria-label="{{ __('projects.capability_clear_aria') }}"
                    class="inline-flex items-center rounded-r-lg border border-l-0 border-equator-dark bg-equator-dark px-2.5 text-white/70 transition-colors hover:text-white">
                    <i class="bi bi-x-lg text-xs"></i>
                </a>
            @endif
        </div>

        {{-- ── Backdrop (mobile only) ── --}}
        <div x-show="open" x-cloak x-transition.opacity @click="close()"
            class="fixed inset-0 z-40 bg-black/40 sm:hidden"></div>

        {{-- ── Panel: bottom-sheet (mobile) → popover (desktop) ── --}}
        <div x-show="open" x-cloak @click.outside="close()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-6 opacity-0 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
            class="fixed inset-x-0 bottom-0 z-50 flex max-h-[82vh] flex-col rounded-t-2xl border border-slate-200 bg-white p-4 shadow-2xl
                   sm:absolute sm:inset-x-auto sm:bottom-auto sm:right-0 sm:mt-2 sm:max-h-none sm:w-[40rem] sm:rounded-xl sm:p-5">

            {{-- grab handle (mobile) --}}
            <div class="mx-auto mb-3 h-1 w-10 shrink-0 rounded-full bg-slate-200 sm:hidden"></div>

            {{-- header --}}
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <p class="text-[0.62rem] font-bold uppercase tracking-[0.2em] text-equator-orange">{{ __('projects.capability_panel_eyebrow') }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ __('projects.capability_panel_subtitle') }}</p>
                </div>
                <button type="button" @click="close()" class="-mr-1 -mt-1 rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 sm:hidden">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            {{-- search --}}
            <div class="relative mb-4 shrink-0">
                <i class="bi bi-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input x-ref="search" x-model="q" type="text" placeholder="{{ __('projects.capability_search_placeholder') }}"
                    @keydown.enter.prevent
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-3 text-sm text-equator-text placeholder-slate-400 focus:border-equator-dark focus:bg-white focus:outline-none focus:ring-2 focus:ring-equator-dark/15">
            </div>

            {{-- ════ DESKTOP: two-pane ════ --}}
            <div class="hidden gap-5 sm:grid sm:grid-cols-[13.5rem,1fr]">
                {{-- capability rail --}}
                <div class="flex flex-col gap-1 border-r border-slate-100 pr-4" :class="searching && 'pointer-events-none opacity-40'">
                    <template x-for="(g, i) in groups" :key="g.label">
                        <button type="button" @click="activeTab = i"
                            class="rounded-lg px-3 py-2.5 text-left transition-colors"
                            :class="activeTab === i ? 'bg-equator-dark/5' : 'hover:bg-slate-50'">
                            <span class="flex items-center justify-between">
                                <span class="text-sm font-semibold" :class="activeTab === i ? 'text-equator-dark' : 'text-slate-700'" x-text="g.label"></span>
                                <span class="text-[0.65rem] font-bold text-slate-300" x-text="g.count"></span>
                            </span>
                            <span class="mt-0.5 block text-xs leading-snug text-slate-400" x-text="g.desc"></span>
                        </button>
                    </template>
                </div>

                {{-- services / results --}}
                <div class="max-h-80 min-h-[16rem] overflow-y-auto">
                    {{-- browse --}}
                    <template x-if="!searching">
                        <div>
                            <template x-for="svc in current.services" :key="svc.href">
                                <a :href="svc.href"
                                    class="flex items-center justify-between rounded-lg px-3 py-2.5 text-sm transition-colors hover:bg-slate-50"
                                    :class="svc.active ? 'font-semibold text-equator-dark' : 'text-slate-600'">
                                    <span x-text="svc.name"></span>
                                    <i class="bi text-equator-bright" :class="svc.active ? 'bi-check2' : 'bi-arrow-right text-slate-300'"></i>
                                </a>
                            </template>
                        </div>
                    </template>
                    {{-- search results (flat, across all categories) --}}
                    <template x-if="searching">
                        <div>
                            <template x-for="svc in results" :key="svc.href">
                                <a :href="svc.href" class="block rounded-lg px-3 py-2.5 transition-colors hover:bg-slate-50">
                                    <span class="block text-[0.6rem] font-bold uppercase tracking-wider text-slate-400" x-text="svc.group"></span>
                                    <span class="text-sm text-slate-700" x-text="svc.name"></span>
                                </a>
                            </template>
                            <p x-show="results.length === 0" class="px-3 py-10 text-center text-sm text-slate-400">
                                {{ __('projects.no_expertise_match') }} “<span x-text="q"></span>”.
                            </p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- ════ MOBILE: drill-in ════ --}}
            <div class="overflow-y-auto sm:hidden">
                {{-- search results --}}
                <div x-show="searching">
                    <template x-for="svc in results" :key="svc.href">
                        <a :href="svc.href" class="block rounded-lg px-3 py-3 hover:bg-slate-50">
                            <span class="block text-[0.6rem] font-bold uppercase tracking-wider text-slate-400" x-text="svc.group"></span>
                            <span class="text-sm text-slate-700" x-text="svc.name"></span>
                        </a>
                    </template>
                    <p x-show="results.length === 0" class="px-3 py-10 text-center text-sm text-slate-400">
                        {{ __('projects.no_expertise_match') }} “<span x-text="q"></span>”.
                    </p>
                </div>

                {{-- capability cards --}}
                <div x-show="!searching && mobileView === 'menu'" class="space-y-2">
                    <template x-for="(g, i) in groups" :key="g.label">
                        <button type="button" @click="pickCategory(i)"
                            class="flex w-full items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-left transition-colors hover:bg-slate-50">
                            <span>
                                <span class="block text-sm font-semibold text-equator-dark" x-text="g.label"></span>
                                <span class="mt-0.5 block text-xs text-slate-400" x-text="g.desc"></span>
                            </span>
                            <span class="flex items-center gap-2 text-slate-300">
                                <span class="text-[0.65rem] font-bold" x-text="g.count"></span>
                                <i class="bi bi-chevron-right text-xs"></i>
                            </span>
                        </button>
                    </template>
                </div>

                {{-- drilled service list --}}
                <div x-show="!searching && mobileView === 'list'">
                    <button type="button" @click="mobileView = 'menu'"
                        class="mb-2 inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-equator-dark">
                        <i class="bi bi-chevron-left text-[0.65rem]"></i> <span x-text="current.label"></span>
                    </button>
                    <template x-for="svc in current.services" :key="svc.href">
                        <a :href="svc.href"
                            class="flex items-center justify-between rounded-lg px-3 py-3 text-sm transition-colors hover:bg-slate-50"
                            :class="svc.active ? 'font-semibold text-equator-dark' : 'text-slate-600'">
                            <span x-text="svc.name"></span>
                            <i class="bi text-equator-bright" :class="svc.active ? 'bi-check2' : 'bi-arrow-right text-slate-300'"></i>
                        </a>
                    </template>
                </div>
            </div>

            {{-- footer: clear --}}
            @if ($active)
                <div class="mt-3 shrink-0 border-t border-slate-100 pt-3">
                    <a href="{{ $clearHref }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-equator-dark">
                        <i class="bi bi-x-lg text-xs"></i> {{ __('projects.capability_clear') }}
                    </a>
                </div>
            @endif
        </div>

        <script>
            function capabilityFilter() {
                return {
                    open: false,
                    q: '',
                    activeTab: @js($activeTabIndex),
                    mobileView: 'menu', // 'menu' | 'list'
                    groups: @js($capabilityData),

                    get searching() { return this.q.trim() !== ''; },
                    get current() { return this.groups[this.activeTab] || this.groups[0] || { label: '', services: [] }; },
                    get results() {
                        const t = this.q.trim().toLowerCase();
                        const out = [];
                        this.groups.forEach(g => g.services.forEach(s => {
                            if (s.name.toLowerCase().includes(t)) out.push({ ...s, group: g.label });
                        }));
                        return out;
                    },

                    openPanel() { this.open = true; this.$nextTick(() => this.$refs.search?.focus()); },
                    close() { this.open = false; this.q = ''; this.mobileView = 'menu'; },
                    pickCategory(i) { this.activeTab = i; this.mobileView = 'list'; },
                };
            }
        </script>
    </div>
@endif
