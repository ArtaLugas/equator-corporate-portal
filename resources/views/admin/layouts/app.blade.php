<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Admin Panel') · {{ app_setting('company_name', 'Equator Group') }}</title>

    {{-- Favicon (from CMS settings) --}}
    @if (app_setting('favicon'))
        <link rel="icon" href="{{ asset('storage/' . app_setting('favicon')) }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/admin.js'])

    {{-- Responsive 16:9 frame for CKEditor media embeds (YouTube/Vimeo) shown on
         admin detail pages. Mirrors the rule in the public layout; inline so it
         works without a Vite rebuild. HTML Purifier strips the `position` the
         inline 16:9 hack needs, which would otherwise collapse the box to 0px. --}}
    <style>
        figure.media { margin: 1.75rem 0; }
        figure.media > div,
        figure.media > div > div {
            position: static !important;
            height: auto !important;
            padding: 0 !important;
        }
        figure.media iframe {
            display: block;
            width: 100% !important;
            height: auto !important;
            aspect-ratio: 16 / 9;
            border: 0;
            border-radius: 0.5rem;
        }
    </style>

</head>

<body x-data="{ sidebarOpen: false }" class="flex h-full bg-gray-100 text-gray-800 antialiased">
    {{-- Sidebar --}}
    @include('admin.partials.sidebar')

    {{-- Main Content --}}
    <div class="flex min-h-screen w-full flex-col lg:ml-72">
        {{-- Topbar --}}
        @include('admin.partials.topbar')

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto p-6 lg:p-8">
            {{-- Flash Message --}}
            <x-admin.alert />

            {{-- Content --}}
            @yield('content')
        </main>
    </div>

    {{-- Modal Root --}}
    <x-admin.modal />

    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => window.lucide && window.lucide.createIcons());
    </script>
</body>

</html>
