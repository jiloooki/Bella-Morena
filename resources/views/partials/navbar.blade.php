@php
    $navItems = [
        ['label' => 'Home', 'route' => route('index'), 'active' => request()->routeIs('index')],
        ['label' => 'Movies', 'route' => route('movies'), 'active' => request()->routeIs('movies', 'movie')],
        ['label' => 'TV Shows', 'route' => route('tvshows'), 'active' => request()->routeIs('tvshows', 'tv', 'episode')],
        ['label' => 'New & Popular', 'route' => route('trending'), 'active' => request()->routeIs('trending')],
        [
            'label' => 'My List',
            'route' => auth()->check() ? route('profile.watchlist', ['username' => auth()->user()->username]) : route('login'),
            'active' => request()->routeIs('profile.watchlist'),
        ],
    ];
@endphp

<header
    x-data="{ mobileOpen: false, solid: window.scrollY > 24, profileOpen: false }"
    x-init="
        const handleScroll = () => solid = window.scrollY > 24;
        window.addEventListener('scroll', handleScroll);
        $watch('mobileOpen', value => document.body.classList.toggle('overflow-hidden', value));
    "
    @keydown.escape.window="mobileOpen = false"
    class="bella-navbar"
    :class="{ 'is-solid': solid || mobileOpen }"
>
    <div class="bella-nav-shell">
        <div class="bella-nav-inner">
            <div class="bella-brand-wrap flex items-center gap-4">
                <button
                    class="bella-icon-button bella-mobile-trigger lg:hidden"
                    :class="{ 'is-active': mobileOpen }"
                    @click="mobileOpen = !mobileOpen"
                    type="button"
                    aria-label="Open menu"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" d="M3 6h18M3 12h18M3 18h18"/>
                    </svg>
                </button>

                <a href="{{ route('index') }}" class="bella-brand">
                    Bella Morena
                </a>
            </div>

            <nav class="bella-nav-links hidden lg:flex">
                @foreach($navItems as $item)
                    <a href="{{ $item['route'] }}" class="bella-nav-link {{ $item['active'] ? 'is-active' : '' }}">
                        {{ __($item['label']) }}
                    </a>
                @endforeach
            </nav>

            <div class="bella-nav-actions">
                <button
                    type="button"
                    class="bella-icon-button"
                    @click.prevent="searchOpen = true; if (searchOpen) $nextTick(()=>{$refs.searchInput.focus()});"
                    aria-controls="search-modal"
                    aria-label="Search"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                        <circle cx="11" cy="11" r="6.5"></circle>
                        <path stroke-linecap="round" d="M16 16l5 5"></path>
                    </svg>
                </button>

                <button type="button" class="bella-icon-button bella-hide-mobile" aria-label="Notifications">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17H5.5A1.5 1.5 0 0 1 4 15.5v-.36a2 2 0 0 1 .56-1.39l1.07-1.12A4 4 0 0 0 6.75 9.9V8.5a5.25 5.25 0 1 1 10.5 0v1.4a4 4 0 0 0 1.12 2.73l1.07 1.12a2 2 0 0 1 .56 1.39v.36a1.5 1.5 0 0 1-1.5 1.5H18"></path>
                        <path stroke-linecap="round" d="M9.5 20a2.5 2.5 0 0 0 5 0"></path>
                    </svg>
                </button>

                @auth
                    @if(auth()->user()->account_type == 'admin')
                        <a href="{{ route('admin.index') }}" target="_blank" class="bella-icon-button hidden sm:inline-flex" aria-label="Admin">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h9v9"></path>
                            </svg>
                        </a>
                    @endif

                    <div class="relative">
                        <button
                            class="bella-avatar-button"
                            aria-haspopup="true"
                            @click.prevent="profileOpen = !profileOpen"
                            :aria-expanded="profileOpen"
                        >
                            {!! gravatar(Auth::user()->name,Auth::user()->avatarurl,'h-9 w-9 rounded-md bg-[#E50914] text-xs font-bold flex items-center justify-center text-white overflow-hidden') !!}
                        </button>

                        <div
                            x-show="profileOpen"
                            @click.outside="profileOpen = false"
                            @keydown.escape.window="profileOpen = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2"
                            class="bella-profile-menu"
                            style="display: none;"
                        >
                            <a href="{{ route('profile', Auth::user()->username) }}" class="bella-profile-link">{{ __('My profile') }}</a>
                            <a href="{{ route('profile.watchlist', ['username' => Auth::user()->username]) }}" class="bella-profile-link">{{ __('My List') }}</a>
                            <a href="{{ route('profile.history', ['username' => Auth::user()->username]) }}" class="bella-profile-link">{{ __('Watch history') }}</a>
                            <a href="{{ route('settings') }}" class="bella-profile-link">{{ __('Settings') }}</a>
                            <a href="{{ route('logout') }}" class="bella-profile-link">{{ __('Logout') }}</a>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="bella-avatar-placeholder" aria-label="Sign in">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                        </svg>
                    </a>
                @endauth
            </div>
        </div>

    </div>

    <div
        class="bella-mobile-overlay lg:hidden"
        x-show="mobileOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="mobileOpen = false"
        style="display: none;"
    ></div>

    <aside
        class="bella-mobile-drawer lg:hidden"
        x-show="mobileOpen"
        x-cloak
        x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        @click.outside="mobileOpen = false"
        role="dialog"
        aria-modal="true"
        style="display: none;"
    >
        <div class="bella-mobile-drawer-inner">
            <div class="bella-mobile-drawer-head">
                <a href="{{ route('index') }}" class="bella-brand" @click="mobileOpen = false">
                    Bella Morena
                </a>

                <button
                    type="button"
                    class="bella-icon-button bella-mobile-close"
                    @click="mobileOpen = false"
                    aria-label="Close menu"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/>
                    </svg>
                </button>
            </div>

            <nav class="bella-mobile-links" aria-label="Mobile navigation">
                @foreach($navItems as $item)
                    <a href="{{ $item['route'] }}" class="bella-mobile-link {{ $item['active'] ? 'is-active' : '' }}" @click="mobileOpen = false">
                        {{ __($item['label']) }}
                    </a>
                @endforeach
            </nav>
        </div>
    </aside>
</header>
