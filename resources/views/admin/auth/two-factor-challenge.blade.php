<!DOCTYPE html>
<html lang="en" class="h-full bg-equator-bg">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @if (app_setting('favicon'))
        <link rel="icon" href="{{ asset('storage/' . app_setting('favicon')) }}">
    @endif
    <title>Two-Factor Verification &mdash; {{ app_setting('company_name', 'Equator Group') }}</title>
</head>

<body class="flex h-full min-h-screen flex-col justify-center p-4 text-equator-text antialiased">

    <div class="mx-auto w-full max-w-[420px]" x-data="{ recovery: false }">

        <div class="mb-12 flex flex-col items-center text-center">
            @if (app_setting('logo'))
                <div class="mb-8 flex h-14 justify-center">
                    <img src="{{ asset('storage/' . app_setting('logo')) }}"
                        alt="{{ app_setting('company_name', 'Equator Group') }}"
                        class="h-full w-auto max-w-[280px] object-contain opacity-95">
                </div>
            @endif
            <h1 class="font-serif text-2xl font-light tracking-tight text-slate-950 sm:text-3xl">Two-Factor Verification</h1>
            <div class="mt-4 flex items-center gap-3 text-[10px] font-bold uppercase tracking-[0.25em]">
                <span class="text-slate-400">Secure Login</span>
                <span class="h-px w-4 bg-slate-200"></span>
                <span class="text-slate-900">{{ app_setting('company_name', 'Equator Group') }}</span>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 p-4 text-sm text-red-700">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 shrink-0 text-red-500"><circle cx="12" cy="12" r="10" /><line x1="12" x2="12" y1="8" y2="12" /><line x1="12" x2="12" y1="16" y2="16" /></svg>
                <p class="flex-1 font-medium">{{ $errors->first() }}</p>
            </div>
        @endif

        <div class="shadow-enterprise relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-8">
            <div class="absolute left-0 top-0 h-1 w-full bg-gradient-to-r from-equator-dark via-equator-bright to-equator-light"></div>

            <p class="mb-6 text-sm font-medium leading-relaxed text-gray-500" x-show="!recovery">
                Enter the 6-digit code from your authenticator app to finish signing in.
            </p>
            <p class="mb-6 text-sm font-medium leading-relaxed text-gray-500" x-show="recovery" x-cloak>
                Enter one of your one-time recovery codes.
            </p>

            <form action="{{ route('admin.two-factor.login.store') }}" method="POST" class="space-y-5">
                @csrf

                <input type="text" name="code" autocomplete="one-time-code" autofocus
                    :inputmode="recovery ? 'text' : 'numeric'"
                    :placeholder="recovery ? 'XXXX-XXXX' : '123456'"
                    class="block w-full rounded-xl border border-gray-200 bg-gray-50/50 py-3 px-4 text-center font-mono text-lg tracking-widest text-equator-text focus:border-equator-bright focus:bg-white focus:outline-none focus:ring-2 focus:ring-equator-bright/50"
                    required>

                <button type="submit"
                    class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-equator-dark text-sm font-bold text-white shadow-lg shadow-equator-dark/25 transition-all hover:bg-equator-bright focus:outline-none focus:ring-2 focus:ring-equator-bright focus:ring-offset-2 active:scale-[0.98]">
                    Verify &amp; Continue
                </button>
            </form>

            <button type="button" @click="recovery = !recovery"
                class="mt-5 w-full text-center text-xs font-bold text-equator-bright transition-colors hover:text-equator-dark">
                <span x-show="!recovery">Use a recovery code instead</span>
                <span x-show="recovery" x-cloak>Use your authenticator app instead</span>
            </button>
        </div>

        <p class="mt-8 text-center">
            <a href="{{ route('admin.login') }}" class="text-sm font-bold text-gray-400 transition-colors hover:text-gray-600">&larr; Cancel and return to sign in</a>
        </p>
    </div>

</body>

</html>
