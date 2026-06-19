@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard Overview')

@section('content')

{{-- PAGE HEADER --}}
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">

    <div>

        <h1 class="text-3xl font-black tracking-tight text-gray-900">

            Enterprise Dashboard

        </h1>

        <p class="text-gray-500 mt-2">

            Verify reusable UI architecture and admin layout system.

        </p>

    </div>

    <div class="flex items-center gap-3">

        <a href="{{ route('admin.dashboard.report') }}" target="_blank" rel="noopener"
            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800 transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7z" /><path d="M14 2v5h5" />
                <path d="M9 13h6" /><path d="M9 17h3" />
            </svg>
            Generate Report
        </a>

        <a href="{{ route('admin.dashboard.export') }}"
            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-gray-300 bg-white text-sm font-semibold hover:bg-gray-50 transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><polyline points="7 10 12 15 17 10" /><line x1="12" x2="12" y1="15" y2="3" />
            </svg>
            Export Excel
        </a>

    </div>

</div>

{{-- STATS --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">

    {{-- SERVICES --}}
    <a href="{{ route('admin.services.index') }}" class="block">
        <x-admin.stat-card title="Services" :value="number_format($stats['services'] ?? 0)" color="bright" sub="Active service offerings">
            <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </x-slot>
        </x-admin.stat-card>
    </a>

    {{-- MESSAGES --}}
    <a href="{{ route('admin.messages.index') }}" class="block">
        <x-admin.stat-card title="Messages" :value="number_format($stats['messages'] ?? 0)" color="orange"
            :sub="($stats['unread_messages'] ?? 0) . ' unread'">
            <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
            </x-slot>
        </x-admin.stat-card>
    </a>

    {{-- PROJECTS --}}
    <a href="{{ route('admin.projects.index') }}" class="block">
        <x-admin.stat-card title="Projects" :value="number_format($stats['projects'] ?? 0)" color="primary" sub="Portfolio projects">
            <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>
            </x-slot>
        </x-admin.stat-card>
    </a>

    @if (auth('admin')->user()?->isSuperAdmin())
        {{-- USERS (super admin only) --}}
        <a href="{{ route('admin.admins.index') }}" class="block">
            <x-admin.stat-card title="Users" :value="number_format($stats['users'] ?? 0)" color="success" sub="Admin accounts">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </x-slot>
            </x-admin.stat-card>
        </a>
    @else
        {{-- NEWS (regular admin) --}}
        <a href="{{ route('admin.news.index') }}" class="block">
            <x-admin.stat-card title="News" :value="number_format($stats['news'] ?? 0)" color="success"
                :sub="($stats['published_news'] ?? 0) . ' published'">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>
                </x-slot>
            </x-admin.stat-card>
        </a>
    @endif

</div>

{{-- ANALYTICS + ACTIVITY --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    {{-- ANALYTICS --}}
    <div
        x-data="visitorAnalyticsChart()"
        x-init="initChart()"
        class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6"
    >

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">

            <div>

                <h3 class="text-lg font-bold text-gray-900">

                    Visitor Analytics

                </h3>

                <p class="text-sm text-gray-500 mt-1">

                    Last 12 months · {{ number_format($chart['total_views'] ?? 0) }} views ·
                    {{ number_format($chart['total_visitors'] ?? 0) }} unique visitors

                </p>

            </div>

            {{-- LEGEND --}}
            <div class="flex items-center gap-4 text-xs">

                <div class="flex items-center gap-2">

                    <span class="h-2.5 w-2.5 rounded-full bg-blue-600"></span>

                    <span class="text-gray-500">

                        Visitors

                    </span>

                </div>

                <div class="flex items-center gap-2">

                    <span class="h-2.5 w-2.5 rounded-full bg-orange-400"></span>

                    <span class="text-gray-500">

                        Pageviews

                    </span>

                </div>

            </div>

        </div>

        {{-- CHART --}}
        <div
            x-ref="chart"
            class="h-[320px]"
        ></div>

    </div>

    {{-- RECENT ACTIVITY --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-6">

            <div>

                <h3 class="text-lg font-bold text-gray-900">

                    Recent Activity

                </h3>

                <p class="text-sm text-gray-500 mt-1">

                    Latest administrator activities

                </p>

            </div>

            @if (auth('admin')->user()?->isSuperAdmin())
                <a href="{{ route('admin.activity-logs.index') }}"
                    class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition">
                    View all
                </a>
            @endif

        </div>

        {{-- ACTIVITY LIST --}}
        <div class="space-y-1">

            @forelse($recentActivities as $activity)

                <div class="flex gap-3 p-3 rounded-xl hover:bg-gray-50 transition">

                    {{-- ICON --}}
                    <div class="h-10 w-10 rounded-xl bg-equator-dark/5 text-equator-dark flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3v16a2 2 0 0 0 2 2h16" /><path d="m19 9-5 5-4-4-3 3" />
                        </svg>
                    </div>

                    {{-- CONTENT --}}
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-gray-700 leading-relaxed">
                            <span class="font-semibold text-gray-900">{{ $activity->admin?->name ?? 'System' }}</span>
                            @if ($activity->module)
                                <span class="mx-1 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-bold text-gray-500">{{ $activity->module }}</span>
                            @endif
                        </p>
                        <p class="text-sm text-gray-600">{{ $activity->description }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $activity->created_at?->diffForHumans() }}</p>
                    </div>

                </div>

            @empty

                <div class="py-10 text-center">
                    @if (auth('admin')->user()?->isSuperAdmin())
                        <p class="text-sm font-medium text-gray-400">No activity recorded yet.</p>
                    @else
                        <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-xl bg-gray-50 text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" /></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-400">Activity log is restricted to super admins.</p>
                    @endif
                </div>

            @endforelse

        </div>

    </div>

</div>

@endsection

@push('scripts')

<script>

    function visitorAnalyticsChart() {

        return {

            chart: null,

            initChart() {

                this.chart = new ApexCharts(

                    this.$refs.chart,

                    {

                        chart: {
                            type: 'area',
                            height: 320,
                            toolbar: {
                                show: false
                            },
                            zoom: {
                                enabled: false
                            },
                            fontFamily: 'inherit',
                        },

                        series: [

                            {
                                name: 'Visitors',
                                data: @json($chart['visitors'] ?? [])
                            },

                            {
                                name: 'Pageviews',
                                data: @json($chart['views'] ?? [])
                            }

                        ],

                        colors: [
                            '#2563eb',
                            '#fb923c'
                        ],

                        stroke: {
                            curve: 'smooth',
                            width: [3, 2]
                        },

                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.35,
                                opacityTo: 0.03,
                            }
                        },

                        grid: {
                            borderColor: '#f1f5f9',
                            strokeDashArray: 4,
                        },

                        dataLabels: {
                            enabled: false
                        },

                        xaxis: {

                            categories: @json($chart['labels'] ?? []),

                            labels: {
                                style: {
                                    colors: '#94a3b8',
                                    fontSize: '11px'
                                }
                            }
                        },

                        yaxis: {

                            labels: {

                                style: {
                                    colors: '#94a3b8',
                                    fontSize: '11px'
                                }
                            }
                        },

                        legend: {
                            show: false
                        },

                        tooltip: {

                            theme: 'light',

                            style: {
                                fontSize: '12px'
                            }
                        }

                    }
                );

                this.chart.render();
            }
        }
    }

</script>

@endpush
