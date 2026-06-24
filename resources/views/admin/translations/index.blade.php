@extends('admin.layouts.app')

@section('title', 'Translation Progress')
@section('page-title', 'Translation Progress')

@section('content')

    @php
        $localeName = config('locales.supported.' . $locale . '.native', strtoupper($locale));
    @endphp

    {{-- HEADER --}}
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold tracking-tight text-equator-text">Translation Progress</h1>
        <p class="mt-1.5 text-sm font-medium text-gray-500">
            How much of each module is translated into <strong>{{ $localeName }}</strong> ({{ strtoupper($locale) }}).
            Untranslated content automatically falls back to the default language on the public site.
        </p>
    </div>

    {{-- OVERALL --}}
    <div class="mb-8 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Overall ({{ strtoupper($locale) }})</p>
                <p class="mt-1 text-3xl font-extrabold text-equator-text">{{ $overall }}%</p>
            </div>
            <div>
                @if ($complete)
                    <span class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                        All modules complete — ready to publish
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 rounded-xl bg-amber-50 px-4 py-2 text-sm font-bold text-amber-700">
                        In progress
                    </span>
                @endif
            </div>
        </div>
        <div class="mt-4 h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
            <div class="h-full rounded-full {{ $overall === 100 ? 'bg-emerald-500' : 'bg-equator-bright' }} transition-all"
                style="width: {{ max($overall, 2) }}%"></div>
        </div>
    </div>

    {{-- PER-MODULE --}}
    <x-admin.table>
        <x-admin.table-head>
            <x-admin.th>Module</x-admin.th>
            <x-admin.th>Progress</x-admin.th>
            <x-admin.th>Complete</x-admin.th>
            <x-admin.th>Partial</x-admin.th>
            <x-admin.th>Untranslated</x-admin.th>
            <x-admin.th class="text-right">Action</x-admin.th>
        </x-admin.table-head>

        <x-admin.table-body>
            @foreach ($rows as $row)
                @php
                    $pct = $row['percent'];
                    $barColor = $pct === 100 ? 'bg-emerald-500' : ($pct === 0 ? 'bg-gray-300' : 'bg-amber-400');
                @endphp
                <tr class="group transition-colors hover:bg-gray-50/50">

                    <x-admin.td>
                        <p class="font-bold text-gray-900">{{ $row['label'] }}</p>
                        <p class="mt-0.5 text-xs font-medium text-gray-400">{{ $row['total'] }} record(s)</p>
                    </x-admin.td>

                    <x-admin.td class="w-64">
                        <div class="flex items-center gap-3">
                            <div class="h-2 w-40 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $row['total'] ? max($pct, 2) : 0 }}%"></div>
                            </div>
                            <span class="text-xs font-bold {{ $pct === 100 ? 'text-emerald-600' : ($pct === 0 ? 'text-gray-400' : 'text-amber-600') }}">{{ $pct }}%</span>
                        </div>
                    </x-admin.td>

                    <x-admin.td>
                        <span class="text-sm font-bold text-emerald-700">{{ $row['complete'] }}</span>
                        <span class="text-xs text-gray-400">/ {{ $row['total'] }}</span>
                    </x-admin.td>

                    <x-admin.td>
                        <span class="text-sm font-medium {{ $row['partial'] ? 'text-amber-600' : 'text-gray-300' }}">{{ $row['partial'] ?: '—' }}</span>
                    </x-admin.td>

                    <x-admin.td>
                        <span class="text-sm font-medium {{ $row['untranslated'] ? 'text-gray-600' : 'text-gray-300' }}">{{ $row['untranslated'] ?: '—' }}</span>
                    </x-admin.td>

                    <x-admin.td class="text-right">
                        <a href="{{ route($row['route']) }}"
                            class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-bold text-equator-dark transition hover:border-equator-dark">
                            Open
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14" /><path d="m12 5 7 7-7 7" /></svg>
                        </a>
                    </x-admin.td>

                </tr>
            @endforeach
        </x-admin.table-body>
    </x-admin.table>

    <p class="mt-6 text-xs font-medium text-gray-400">
        Tip: open a module and use the <strong>Translation</strong> column to find records that still need
        {{ strtoupper($locale) }}, then fill the <strong>{{ strtoupper($locale) }}</strong> tab when editing.
        Or run <code class="rounded bg-gray-100 px-1.5 py-0.5">php artisan i18n:status</code>.
    </p>

@endsection
