@extends('admin.layouts.app')

@section('title', 'Credential')
@section('page-title', 'Credential')

@section('content')

    <div class="mb-6 flex items-center justify-between gap-4">
        <a href="{{ route('admin.company-credentials.index') }}"
            class="text-sm font-semibold text-gray-500 hover:text-gray-900">&larr; Back to credentials</a>
        <a href="{{ route('admin.company-credentials.edit', $credential) }}"
            class="rounded-xl bg-equator-orange px-5 py-2.5 text-sm font-bold text-white hover:opacity-90">Edit</a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span
                            class="inline-flex items-center rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">{{ $credential->categoryLabel() }}</span>
                        <h1 class="mt-3 text-xl font-extrabold text-equator-text">{{ $credential->title }}</h1>
                        @if ($credential->issuer)
                            <p class="mt-1 text-sm font-medium text-gray-500">{{ $credential->issuer }}</p>
                        @endif
                    </div>
                    @if ($credential->image)
                        <img src="{{ asset('storage/' . $credential->image) }}"
                            class="h-24 w-32 rounded-xl border border-gray-200 object-cover">
                    @endif
                </div>

                @if (filled(strip_tags((string) $credential->description)))
                    <div class="prose prose-sm mt-6 max-w-none border-t border-gray-50 pt-6">
                        {!! $credential->description !!}
                    </div>
                @endif
            </div>

            @if ($credential->items->isNotEmpty())
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-sm font-extrabold uppercase tracking-wide text-gray-500">Items
                        ({{ $credential->items->count() }})</h2>
                    <ul class="divide-y divide-gray-50">
                        @foreach ($credential->items as $it)
                            <li class="py-2.5">
                                <p class="text-sm font-semibold text-gray-800">{{ $it->title }}</p>
                                @if ($it->description)
                                    <p class="text-xs text-gray-500">{{ $it->description }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">Credential Number</dt>
                        <dd class="font-medium text-gray-800">{{ $credential->credential_number ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">Issue Date</dt>
                        <dd class="font-medium text-gray-800">{{ $credential->issue_date?->format('d M Y') ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">Expiry Date</dt>
                        <dd class="font-medium text-gray-800">{{ $credential->expiry_date?->format('d M Y') ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">Status</dt>
                        <dd class="font-medium text-gray-800">{{ __('credentials.status.' . $credential->displayStatus()) }}
                        </dd>
                    </div>
                    @if ($credential->verification_url)
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">Verification</dt>
                            <dd><a href="{{ $credential->verification_url }}" target="_blank" rel="noopener"
                                    class="font-semibold text-equator-bright underline">Open link</a></dd>
                        </div>
                    @endif
                    @if ($credential->attachment)
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-gray-400">Certificate</dt>
                            <dd><a href="{{ asset('storage/' . $credential->attachment) }}" target="_blank"
                                    rel="noopener" class="font-semibold text-equator-bright underline">Download PDF</a></dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <x-admin.translation-status :model="$credential" />
            </div>
        </div>

    </div>

@endsection
