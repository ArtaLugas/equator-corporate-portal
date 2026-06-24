@extends('admin.layouts.app')

@section('title', 'Lead Analytics')
@section('page-title', 'Lead Analytics')

@section('content')

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-gray-900">Lead Analytics</h1>
                <p class="mt-1.5 text-sm font-medium text-gray-500">
                    {{ number_format($data['total']) }} leads captured (spam excluded).
                </p>
            </div>
            <a href="{{ route('admin.messages.index') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-bold text-gray-600 transition-colors hover:bg-gray-50 sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6" /></svg>
                Back to Inbox
            </a>
        </div>

        {{-- Leads per Month --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6">
            <h2 class="mb-5 text-sm font-extrabold tracking-tight text-gray-900">Leads per Month</h2>
            @php($monthMax = collect($data['leads_per_month'])->max() ?: 1)
            <div class="flex items-end gap-2 sm:gap-3">
                @foreach ($data['leads_per_month'] as $ym => $count)
                    <div class="flex flex-1 flex-col items-center gap-2" role="img"
                        aria-label="{{ $count }} leads in {{ \Illuminate\Support\Carbon::parse($ym . '-01')->format('F Y') }}">
                        <span class="text-xs font-bold {{ $count ? 'text-gray-700' : 'text-gray-300' }}" aria-hidden="true">{{ $count }}</span>
                        <div class="w-full rounded-t-md bg-equator-bright/80" aria-hidden="true"
                            style="height: {{ $count ? max(6, round($count / $monthMax * 120)) : 2 }}px"></div>
                        <span class="text-[10px] font-medium text-gray-400" aria-hidden="true">{{ \Illuminate\Support\Carbon::parse($ym . '-01')->format('M') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Ranked breakdowns --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            @include('admin.messages._ranked', ['title' => 'Top Landing Pages', 'items' => $data['top_landing_pages']])
            @include('admin.messages._ranked', ['title' => 'Top Campaign', 'items' => $data['top_campaigns']])
            @include('admin.messages._ranked', ['title' => 'Top Referrer', 'items' => $data['top_referrers']])
            @include('admin.messages._ranked', ['title' => 'Top Locale', 'items' => $data['top_locales']])
        </div>

    </div>

@endsection
