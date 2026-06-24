{{-- Renders a list of legal sections (heading + paragraphs + optional bullets).
     Expects: $sections (array), $company (string). --}}
@php($company = $company ?? app_setting('company_name', 'Equator Group'))

<div class="mt-12 space-y-10">
    @foreach ($sections as $s)
        <div>
            <h2 class="font-heading text-lg font-bold text-equator-dark">{{ $s['heading'] }}</h2>

            @foreach ($s['body'] ?? [] as $para)
                <p class="mt-3 text-sm leading-7 text-equator-text/80">{{ str_replace(':company', $company, $para) }}</p>
            @endforeach

            @if (! empty($s['list']))
                <ul class="mt-3 space-y-2 text-sm leading-7 text-equator-text/80">
                    @foreach ($s['list'] as $li)
                        <li class="flex gap-3">
                            <span class="mt-[0.6rem] h-1 w-1 shrink-0 rounded-full bg-equator-bright" aria-hidden="true"></span>
                            <span>{{ str_replace(':company', $company, $li) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endforeach
</div>
