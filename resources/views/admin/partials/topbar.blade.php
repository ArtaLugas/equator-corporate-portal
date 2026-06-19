<header
    class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-gray-200/80 bg-white/90 px-4 backdrop-blur-md lg:px-8">
    {{-- LEFT: Mobile Toggle & Breadcrumbs/Title --}}
    <div class="flex items-center gap-4 lg:gap-6">

        {{-- Mobile Hamburger Menu --}}
        <button @click="sidebarOpen = !sidebarOpen"
            class="rounded-lg p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-900 focus:outline-none lg:hidden">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="4" x2="20" y1="12" y2="12" />
                <line x1="4" x2="20" y1="6" y2="6" />
                <line x1="4" x2="20" y1="18" y2="18" />
            </svg>
        </button>

        {{-- Page Title / Breadcrumbs (Tersembunyi di layar sangat kecil) --}}
        <div class="hidden items-center gap-2 sm:flex">
            <span class="text-sm font-medium text-gray-400">Admin</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="text-gray-300">
                <path d="m9 18 6-6-6-6" />
            </svg>
            <h2 class="text-sm font-bold tracking-tight text-gray-900">
                @yield('page-title', 'Dashboard')
            </h2>
        </div>
    </div>

    {{-- RIGHT: Actions & User Profile --}}
    <div class="flex items-center gap-3 sm:gap-4">

        {{-- Notification Bell --}}
        <div x-data="{ notifyOpen: false }" class="relative">
            <button @click="notifyOpen = !notifyOpen" @click.outside="notifyOpen = false"
                class="relative rounded-full p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-900 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                </svg>
                {{-- Unread badge --}}
                @if (($notifUnreadCount ?? 0) > 0)
                    <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-equator-bright px-1 text-[9px] font-bold text-white ring-2 ring-white">
                        {{ $notifUnreadCount > 9 ? '9+' : $notifUnreadCount }}
                    </span>
                @endif
            </button>

            {{-- Notification Dropdown --}}
            <div x-show="notifyOpen" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2" x-cloak
                class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl shadow-gray-200/50">

                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                    <h4 class="text-sm font-bold text-gray-900">Notifications</h4>
                    @if (($notifUnreadCount ?? 0) > 0)
                        <form action="{{ route('admin.notifications.read-all') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="text-[11px] font-bold text-equator-bright hover:text-equator-dark">
                                Mark all read
                            </button>
                        </form>
                    @endif
                </div>

                <div class="max-h-80 overflow-y-auto">
                    @forelse ($notifItems ?? [] as $notif)
                        <a href="{{ route('admin.notifications.read', $notif->id) }}" rel="nofollow"
                            class="{{ is_null($notif->read_at) ? 'bg-equator-bright/[0.04]' : '' }} flex gap-3 border-b border-gray-50 px-4 py-3 transition-colors hover:bg-gray-50">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-equator-dark/5 text-equator-dark">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7" /><rect x="2" y="4" width="20" height="16" rx="2" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-bold text-gray-900">{{ $notif->data['title'] ?? 'Notification' }}</p>
                                <p class="truncate text-xs text-gray-500">{{ $notif->data['body'] ?? '' }}</p>
                                <p class="mt-0.5 text-[10px] font-medium text-gray-400">{{ $notif->created_at?->diffForHumans() }}</p>
                            </div>
                            @if (is_null($notif->read_at))
                                <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-equator-bright"></span>
                            @endif
                        </a>
                    @empty
                        <div class="p-8 text-center text-sm font-medium text-gray-400">No notifications yet.</div>
                    @endforelse
                </div>

                <a href="{{ route('admin.notifications.index') }}"
                    class="block border-t border-gray-100 px-4 py-3 text-center text-xs font-bold text-equator-dark hover:bg-gray-50">
                    View all notifications
                </a>
            </div>
        </div>

        {{-- Vertical Divider --}}
        <div class="hidden h-5 w-px bg-gray-200 sm:block"></div>

        {{-- User Profile Menu --}}
        <div x-data="{ profileOpen: false }" class="relative">
            <button @click="profileOpen = !profileOpen" @click.outside="profileOpen = false"
                class="group flex items-center gap-3 rounded-full py-1 pl-1 pr-2 transition-colors hover:bg-gray-100 focus:outline-none">
                {{-- Avatar (photo or initials) --}}
                @if (auth()->guard('admin')->user()->avatar)
                    <img src="{{ asset('storage/' . auth()->guard('admin')->user()->avatar) }}"
                        class="h-7 w-7 rounded-full object-cover" alt="Avatar">
                @else
                    <div
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-900 text-[10px] font-bold text-white">
                        {{ strtoupper(substr(auth()->guard('admin')->user()->name, 0, 2)) }}
                    </div>
                @endif

                {{-- User Info (Hidden on mobile) --}}
                <div class="hidden text-left md:block">
                    <p class="text-xs font-bold leading-none text-gray-900">
                        {{ auth()->guard('admin')->user()->name }}
                    </p>
                    <span
                        class="mt-1 inline-block rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold capitalize text-indigo-600">
                        {{ Str::headline(auth()->guard('admin')->user()->role) }}
                    </span>
                </div>

                {{-- Chevron Down --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                    class="hidden text-gray-400 transition-transform group-hover:text-gray-600 md:block"
                    :class="{ 'rotate-180': profileOpen }">
                    <path d="m6 9 6 6 6-6" />
                </svg>
            </button>

            {{-- Profile Dropdown --}}
            <div x-show="profileOpen" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2" x-cloak
                class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl shadow-gray-200/50">
                {{-- User Header inside Dropdown --}}
                <div class="border-b border-gray-100 p-4">
                    <p class="text-sm font-bold text-gray-900">{{ auth()->guard('admin')->user()->name }}</p>
                    <p class="mt-0.5 truncate text-xs font-medium text-gray-500">
                        {{ auth()->guard('admin')->user()->email ?? 'admin@equator.com' }}</p>
                </div>

                <div class="p-1.5">
                    <a href="{{ route('admin.account.edit') }}"
                        class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-50 hover:text-gray-900">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        My Profile
                    </a>
                </div>

                <div class="border-t border-gray-100 p-1.5">
                    <form action="{{ route('admin.logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit"
                            class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-bold text-red-600 transition-colors hover:bg-red-50">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <polyline points="16 17 21 12 16 7" />
                                <line x1="21" x2="9" y1="12" y2="12" />
                            </svg>
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</header>
