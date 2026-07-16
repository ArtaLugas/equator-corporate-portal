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
    <title>Reset Password &mdash; {{ app_setting('company_name', 'Equator Group') }}</title>
</head>

<body class="flex h-full min-h-screen flex-col justify-center p-4 text-equator-text antialiased">

    <div class="mx-auto w-full max-w-[420px]">

        <div class="mb-12 flex flex-col items-center text-center">
            @if (app_setting('logo'))
                <div class="mb-8 flex h-14 justify-center">
                    <img src="{{ asset('storage/' . app_setting('logo')) }}"
                        alt="{{ app_setting('company_name', 'Equator Group') }}"
                        class="h-full w-auto max-w-[280px] object-contain opacity-95">
                </div>
            @endif
            <h1 class="font-serif text-2xl font-light tracking-tight text-slate-950 sm:text-3xl">Forgot Password</h1>
            <div class="mt-4 flex items-center gap-3 text-[10px] font-bold uppercase tracking-[0.25em]">
                <span class="text-slate-400">Password Recovery</span>
                <span class="h-px w-4 bg-slate-200"></span>
                <span class="text-slate-900">{{ app_setting('company_name', 'Equator Group') }}</span>
            </div>
        </div>

        {{-- Success (generic — no user enumeration) --}}
        @if (session('status'))
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-700">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 shrink-0 text-emerald-500"><path d="M20 6 9 17l-5-5" /></svg>
                <p class="flex-1 font-medium">{{ session('status') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 p-4 text-sm text-red-700">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 shrink-0 text-red-500"><circle cx="12" cy="12" r="10" /><line x1="12" x2="12" y1="8" y2="12" /><line x1="12" x2="12" y1="16" y2="16" /></svg>
                <p class="flex-1 font-medium">{{ $errors->first() }}</p>
            </div>
        @endif

        <div class="shadow-enterprise relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-8">
            <div class="absolute left-0 top-0 h-1 w-full bg-gradient-to-r from-equator-dark via-equator-bright to-equator-light"></div>

            <p class="mb-6 text-sm font-medium leading-relaxed text-gray-500">
                Enter the email address on your account and we'll send you a link to reset your password.
            </p>

            <form action="{{ route('admin.password.email') }}" method="POST" class="space-y-5">
                @csrf

                <div class="space-y-2">
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-gray-600">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="name@company.com" required autocomplete="email"
                        class="block w-full rounded-xl border border-gray-200 bg-gray-50/50 py-3 px-4 text-sm font-medium text-equator-text transition-all placeholder:text-gray-400 focus:border-equator-bright focus:bg-white focus:outline-none focus:ring-2 focus:ring-equator-bright/50">
                </div>

                @if (config('services.turnstile.site_key'))
                    <div class="pt-1">
                        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light" data-language="en"></div>
                    </div>
                @endif

                <button type="submit"
                    class="mt-2 flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-equator-dark text-sm font-bold text-white shadow-lg shadow-equator-dark/25 transition-all hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright focus:ring-offset-2 active:scale-[0.98]">
                    Send Reset Link
                </button>
            </form>
        </div>

        <p class="mt-8 text-center text-sm">
            <a href="{{ route('admin.login') }}" class="font-bold text-equator-bright transition-colors hover:text-equator-dark">&larr; Back to sign in</a>
        </p>
    </div>

</body>

</html>
