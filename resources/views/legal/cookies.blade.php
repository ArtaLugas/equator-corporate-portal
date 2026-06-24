@extends('layouts.public')

@php
    $company = app_setting('company_name', 'Equator Group');
    $c = __('legal.cookies');
    $table = $c['table'];
@endphp

@section('title', $c['title'] . ' — ' . $company)
@section('meta_description', \Illuminate\Support\Str::limit(str_replace(':company', $company, $c['intro']), 155))

@section('content')
    <section class="mx-auto max-w-3xl px-6 py-16 sm:py-24 lg:px-8">

        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-equator-bright">
            {{ __('legal.updated_label') }} — {{ __('legal.updated') }}
        </p>

        <h1 class="mt-3 font-heading text-3xl font-bold tracking-tight text-equator-darker sm:text-4xl">
            {{ $c['title'] }}
        </h1>

        <p class="mt-5 text-base leading-7 text-equator-text/80">
            {{ str_replace(':company', $company, $c['intro']) }}
        </p>

        @include('legal._sections', ['sections' => $c['sections'], 'company' => $company])

        {{-- Cookie table --}}
        <div class="mt-12">
            <h2 class="font-heading text-lg font-bold text-equator-dark">{{ $table['caption'] }}</h2>
            <div class="mt-4 overflow-x-auto border border-slate-200">
                <table class="w-full border-collapse text-left text-xs">
                    <caption class="sr-only">{{ $table['caption'] }}</caption>
                    <thead>
                        <tr class="bg-slate-50 text-equator-darker">
                            @foreach ($table['columns'] as $col)
                                <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="text-equator-text/80">
                        @foreach ($table['rows'] as $row)
                            <tr class="border-t border-slate-100 align-top">
                                <td class="px-4 py-3 font-mono text-[0.7rem] text-equator-dark">{{ $row['name'] }}</td>
                                <td class="px-4 py-3">{{ $row['provider'] }}</td>
                                <td class="px-4 py-3">{{ $row['purpose'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $row['category'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $row['duration'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Manage preferences --}}
        <div class="mt-12 border border-slate-200 bg-slate-50 p-6 sm:p-8">
            <p class="text-sm font-medium text-equator-darker">{{ __('legal.contact_lead') }}</p>
            <div class="mt-4 flex flex-wrap gap-3">
                <button type="button"
                    onclick="window.dispatchEvent(new CustomEvent('open-cookie-preferences'))"
                    class="inline-flex items-center gap-2 bg-equator-dark px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-equator-bright focus:outline-none focus-visible:ring-2 focus-visible:ring-equator-bright focus-visible:ring-offset-2">
                    <i class="bi bi-sliders" aria-hidden="true"></i>
                    {{ __('cookie_consent.preferences') }}
                </button>
                <a href="{{ route('privacy') }}"
                    class="inline-flex items-center gap-2 border border-slate-300 px-5 py-2.5 text-sm font-bold text-equator-dark transition-colors hover:border-equator-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-equator-bright focus-visible:ring-offset-2">
                    {{ __('legal.privacy.title') }}
                </a>
            </div>
        </div>

    </section>
@endsection
