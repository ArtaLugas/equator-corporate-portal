@php
    $cats = config('cookie_consent.categories', []);
    $optional = collect($cats)->reject(fn ($c) => $c['required'] ?? false)->keys()->values()->all();
    $cookieName = config('cookie_consent.cookie_name', 'equator_cookie_consent');
    $version = (int) config('cookie_consent.version', 1);
    $days = (int) config('cookie_consent.lifetime_days', 180);
@endphp

<div x-data="{
        open: false,
        panel: false,
        version: {{ $version }},
        name: '{{ $cookieName }}',
        days: {{ $days }},
        optional: @js($optional),
        choices: {},
        init() {
            this.optional.forEach(k => (this.choices[k] = false));
            const stored = this.read();
            if (!stored || stored.version !== this.version) {
                this.open = true;
            }
            window.addEventListener('open-cookie-preferences', () => {
                this.loadInto();
                this.open = true;
                this.panel = true;
            });
        },
        read() {
            try {
                const m = document.cookie.match(new RegExp('(?:^|; )' + this.name + '=([^;]*)'));
                if (m) return JSON.parse(decodeURIComponent(m[1]));
                const ls = localStorage.getItem(this.name);
                return ls ? JSON.parse(ls) : null;
            } catch (e) { return null; }
        },
        loadInto() {
            const s = this.read();
            if (s && s.categories) this.optional.forEach(k => (this.choices[k] = !!s.categories[k]));
        },
        persist(categories) {
            const payload = { version: this.version, categories: Object.assign({ necessary: true }, categories), ts: Date.now() };
            const val = encodeURIComponent(JSON.stringify(payload));
            const maxAge = this.days * 24 * 60 * 60;
            document.cookie = this.name + '=' + val + '; path=/; max-age=' + maxAge + '; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
            try { localStorage.setItem(this.name, JSON.stringify(payload)); } catch (e) {}
            window.dispatchEvent(new CustomEvent('cookie-consent-updated', { detail: payload.categories }));
            this.open = false;
            this.panel = false;
        },
        acceptAll() { const c = {}; this.optional.forEach(k => (c[k] = true)); this.persist(c); },
        rejectOptional() { const c = {}; this.optional.forEach(k => (c[k] = false)); this.persist(c); },
        saveChoices() { const c = {}; this.optional.forEach(k => (c[k] = !!this.choices[k])); this.persist(c); }
     }"
    x-show="open"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-y-4 opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="translate-y-4 opacity-0"
    role="region"
    aria-label="{{ __('cookie_consent.title') }}"
    class="fixed inset-x-4 bottom-4 z-50 sm:left-6 sm:right-auto sm:max-w-md">

    <div class="border border-slate-200 bg-white p-5 shadow-[0_18px_50px_-20px_rgba(15,23,42,0.4)] sm:p-6">

        {{-- ── Main view ─────────────────────────────────────────────── --}}
        <div x-show="!panel">
            <div class="flex items-center gap-2">
                <span class="inline-block h-2 w-2 rounded-full bg-equator-orange" aria-hidden="true"></span>
                <h2 class="font-heading text-base font-bold text-equator-darker">{{ __('cookie_consent.title') }}</h2>
            </div>

            <p class="mt-3 text-sm leading-6 text-equator-text/80">{{ __('cookie_consent.body') }}</p>

            <a href="{{ route('cookies') }}"
                class="mt-2 inline-block text-xs font-semibold text-equator-bright underline-offset-2 hover:underline">
                {{ __('cookie_consent.policy_link') }}
            </a>

            <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                <button type="button" @click="acceptAll()"
                    class="order-1 inline-flex items-center justify-center bg-equator-dark px-4 py-2.5 text-sm font-bold text-white transition-colors hover:bg-equator-bright focus:outline-none focus-visible:ring-2 focus-visible:ring-equator-bright focus-visible:ring-offset-2 sm:flex-1">
                    {{ __('cookie_consent.accept_all') }}
                </button>
                <button type="button" @click="rejectOptional()"
                    class="order-2 inline-flex items-center justify-center border border-slate-300 px-4 py-2.5 text-sm font-bold text-equator-dark transition-colors hover:border-equator-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-equator-bright focus-visible:ring-offset-2 sm:flex-1">
                    {{ __('cookie_consent.reject_optional') }}
                </button>
                <button type="button" @click="panel = true"
                    class="order-3 inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-equator-text/70 transition-colors hover:text-equator-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-equator-bright focus-visible:ring-offset-2">
                    {{ __('cookie_consent.customize') }}
                </button>
            </div>
        </div>

        {{-- ── Customize panel ──────────────────────────────────────── --}}
        <div x-show="panel" x-cloak>
            <h2 class="font-heading text-base font-bold text-equator-darker">{{ __('cookie_consent.preferences') }}</h2>

            <div class="mt-4 max-h-64 space-y-4 overflow-y-auto pr-1">
                @foreach ($cats as $key => $cfg)
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold text-equator-darker">{{ __('cookie_consent.categories.' . $key . '.label') }}</p>
                            <p class="mt-0.5 text-xs leading-5 text-equator-text/70">{{ __('cookie_consent.categories.' . $key . '.description') }}</p>
                        </div>

                        @if ($cfg['required'] ?? false)
                            <span class="mt-1 shrink-0 whitespace-nowrap text-[0.65rem] font-bold uppercase tracking-wider text-equator-bright">
                                {{ __('cookie_consent.always_on') }}
                            </span>
                        @else
                            <label class="relative mt-1 inline-flex shrink-0 cursor-pointer items-center">
                                <input type="checkbox" x-model="choices['{{ $key }}']" class="peer sr-only"
                                    aria-label="{{ __('cookie_consent.categories.' . $key . '.label') }}">
                                <span class="h-6 w-11 rounded-full bg-slate-200 transition-colors peer-checked:bg-equator-bright peer-focus-visible:ring-2 peer-focus-visible:ring-equator-bright peer-focus-visible:ring-offset-2"></span>
                                <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                            </label>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-5 flex gap-2">
                <button type="button" @click="panel = false"
                    class="inline-flex items-center justify-center border border-slate-300 px-4 py-2.5 text-sm font-bold text-equator-dark transition-colors hover:border-equator-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-equator-bright focus-visible:ring-offset-2">
                    {{ __('cookie_consent.back') }}
                </button>
                <button type="button" @click="saveChoices()"
                    class="inline-flex flex-1 items-center justify-center bg-equator-dark px-4 py-2.5 text-sm font-bold text-white transition-colors hover:bg-equator-bright focus:outline-none focus-visible:ring-2 focus-visible:ring-equator-bright focus-visible:ring-offset-2">
                    {{ __('cookie_consent.save') }}
                </button>
            </div>
        </div>

    </div>
</div>
