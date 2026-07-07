<!DOCTYPE html>
<html lang="en" class="h-full bg-equator-bg">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @if (config('services.turnstile.site_key'))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
    @if (app_setting('favicon'))
        <link rel="icon" href="{{ asset('storage/' . app_setting('favicon')) }}">
    @endif
    <title>Sign In &mdash; {{ app_setting('company_name', 'Equator Group') }}</title>
</head>

<body
    class="flex h-full min-h-screen flex-col justify-center p-4 text-equator-text antialiased selection:bg-equator-bright selection:text-white">

    <div class="mx-auto w-full max-w-[420px]" x-data="{ email: '{{ old('email') }}', password: '', showPwd: false }">

        <!-- Header Branding -->
        <div class="mb-12 flex flex-col items-center text-center">

            @if (app_setting('logo'))
                <div class="mb-8 flex h-14 justify-center">
                    <img src="{{ asset('storage/' . app_setting('logo')) }}"
                        alt="{{ app_setting('company_name', 'Equator Group') }}"
                        class="h-full w-auto max-w-[280px] object-contain opacity-95 [image-rendering:_webkit-optimize-contrast]">
                </div>
            @else
                {{-- Fallback Minimalis Enterprise jika Logo Kosong di CMS --}}
                <div
                    class="relative mb-6 flex h-12 w-12 items-center justify-center border border-slate-200 bg-slate-50 text-slate-900">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path
                            d="M20 13c0 5-3.5 7.5-7.66 9.7a1 1 0 0 1-.68 0C7.5 20.5 4 18 4 13V6a1 1 0 0 1 .76-.97l8-2a1 1 0 0 1 .48 0l8 2A1 1 0 0 1 20 6z" />
                        <path d="m9 12 2 2 4-4" />
                    </svg>
                </div>
            @endif

            {{-- Typography Framework Korporat --}}
            <h1 class="font-serif text-2xl font-light tracking-tight text-slate-950 sm:text-3xl">
                Sign In to Dashboard
            </h1>

            {{-- Subtitle Badge Menjadi Baris Meta Eksklusif --}}
            <div class="mt-4 flex items-center gap-3 text-[10px] font-bold uppercase tracking-[0.25em]">
                <span class="text-slate-400">Management Portal</span>
                <span class="h-px w-4 bg-slate-200"></span>
                <span class="text-slate-900">{{ app_setting('company_name', 'Equator Group') }}</span>
            </div>
        </div>

        <!-- Alert Error -->
        @if ($errors->any())
            <div
                class="mb-6 flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 p-4 text-sm text-red-700 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="mt-0.5 shrink-0 text-red-500">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" x2="12" y1="8" y2="12" />
                    <line x1="12" x2="12" y1="16" y2="16" />
                </svg>
                <div class="flex-1">
                    <p class="mb-1 font-bold">Authentication Failed</p>
                    <p class="text-xs font-medium text-red-600/90">{{ $errors->first() }}</p>
                </div>
            </div>
        @endif

        <!-- Enterprise Card Form -->
        <div class="shadow-enterprise relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-8">
            <!-- Subtle Top Accent Line -->
            <div
                class="absolute left-0 top-0 h-1 w-full bg-gradient-to-r from-equator-dark via-equator-bright to-equator-light">
            </div>

            <form action="{{ route('admin.authenticate') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Input Email -->
                <div class="space-y-2">
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-gray-600">Email
                        Address</label>
                    <div class="relative">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect width="20" height="16" x="2" y="4" rx="2" />
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                            </svg>
                        </div>
                        <input type="email" name="email" id="email" x-model="email"
                            placeholder="name@company.com"
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50/50 py-3 pl-11 pr-4 text-sm font-medium text-equator-text transition-all placeholder:text-gray-400 focus:border-equator-bright focus:bg-white focus:outline-none focus:ring-2 focus:ring-equator-bright/50"
                            required autocomplete="email">
                    </div>
                </div>

                <!-- Input Password -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label for="password"
                            class="block text-xs font-bold uppercase tracking-wider text-gray-600">Password</label>
                        <a href="#"
                            class="text-xs font-bold text-equator-bright transition-colors hover:text-equator-dark">Forgot
                            your password?</a>
                    </div>
                    <div class="relative">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                        </div>
                        <input :type="showPwd ? 'text' : 'password'" name="password" id="password"
                            x-model="password" placeholder="••••••••"
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50/50 py-3 pl-11 pr-11 text-sm font-medium text-equator-text transition-all placeholder:text-gray-400 focus:border-equator-bright focus:bg-white focus:outline-none focus:ring-2 focus:ring-equator-bright/50"
                            required autocomplete="current-password">
                        <button type="button" @click="showPwd = !showPwd"
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 transition-colors hover:text-equator-dark focus:outline-none">
                            <svg x-show="!showPwd" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg x-show="showPwd" x-cloak xmlns="http://www.w3.org/2000/svg" width="18"
                                height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                                <path
                                    d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                                <path d="M6.61 6.61A13.52 13.52 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                                <line x1="2" x2="22" y1="2" y2="22" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center pt-2">
                    <input type="checkbox" name="remember" id="remember"
                        class="h-4 w-4 rounded border-gray-300 bg-white text-equator-dark focus:ring-equator-dark">
                    <label for="remember"
                        class="ml-2.5 cursor-pointer select-none text-xs font-semibold text-gray-500 transition-colors hover:text-equator-text">
                        Keep me signed in
                    </label>
                </div>

                {{-- Cloudflare Turnstile (CAPTCHA) — hanya muncul bila dikonfigurasi --}}
                @if (config('services.turnstile.site_key'))
                    <div class="pt-1">
                        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"
                            data-theme="light" data-language="en"></div>
                    </div>
                @endif

                <!-- Main Button -->
                <button type="submit"
                    class="mt-2 flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-equator-dark text-sm font-bold text-white shadow-lg shadow-equator-dark/25 transition-all hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright focus:ring-offset-2 active:scale-[0.98] active:transform">
                    Go to the Dashboard
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M5 12h14" />
                        <path d="m12 5 7 7-7 7" />
                    </svg>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <p class="mt-10 text-center text-xs font-semibold uppercase tracking-wider text-gray-400">
            &copy; {{ date('Y') }} {{ app_setting('company_name', 'Equator Group') }}. <span
                class="text-gray-300">|</span> Enterprise System
        </p>
    </div>

</body>

</html>
