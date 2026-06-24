@extends('layouts.public')

@php
    $company = app_setting('company_name', 'Equator Group');
    $p = __('legal.privacy');
@endphp

@section('title', $p['title'] . ' — ' . $company)
@section('meta_description', \Illuminate\Support\Str::limit(str_replace(':company', $company, $p['intro']), 155))

@section('content')
    <section class="mx-auto max-w-3xl px-6 py-16 sm:py-24 lg:px-8">

        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-equator-bright">
            {{ __('legal.updated_label') }} — {{ __('legal.updated') }}
        </p>

        <h1 class="mt-3 font-heading text-3xl font-bold tracking-tight text-equator-darker sm:text-4xl">
            {{ $p['title'] }}
        </h1>

        <p class="mt-5 text-base leading-7 text-equator-text/80">
            {{ str_replace(':company', $company, $p['intro']) }}
        </p>

        @include('legal._sections', ['sections' => $p['sections'], 'company' => $company])

        {{-- Contact callout --}}
        <div class="mt-14 border border-slate-200 bg-slate-50 p-6 sm:p-8">
            <p class="text-sm font-medium text-equator-darker">{{ __('legal.contact_lead') }}</p>
            <a href="{{ route('contact') }}"
                class="mt-4 inline-flex items-center gap-2 bg-equator-dark px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-equator-bright focus:outline-none focus-visible:ring-2 focus-visible:ring-equator-bright focus-visible:ring-offset-2">
                {{ __('legal.contact_cta') }}
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </div>

    </section>
@endsection
