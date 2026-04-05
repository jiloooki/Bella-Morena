@extends('layouts.landing')

@section('content')
    <section class="bella-page-hero min-h-[78vh]">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(229,9,20,0.28),transparent_38%)]"></div>

        <div class="bella-shell">
            <div class="bella-hero-inner max-w-4xl mx-auto text-center">
                <div class="bella-kicker justify-center before:hidden">{{ __('') }}</div>

                <h1 class="mx-auto max-w-[900px] text-[1.8rem] font-extrabold leading-[1.15] tracking-[-0.02em] text-white sm:text-[2.2rem] lg:text-[3.2rem]">
                    <span class="whitespace-nowrap">{{ __('Watch Movies & TV Shows') }}</span><br>
                    <span class="whitespace-nowrap">{{ __('100% Pop-Ads Free') }}</span>
                </h1>

                <div class="mx-auto mt-6 max-w-[700px]">
                    <p class="text-base font-medium leading-relaxed text-gray-300 lg:text-xl">
                        {{ __('No redirections. No annoying pop-ups. Just pure, high-quality streaming.') }}
                    </p>

                    <p class="mt-2 text-xs leading-relaxed text-gray-500 lg:text-base">
                        {{ __('Bella Morena is built for fans who value a clean and fast experience.') }}
                    </p>
                </div>

                <div class="mx-auto mt-8 mb-12 max-w-3xl w-full lg:mt-10 lg:mb-14">
                    <form method="post" class="bella-search-panel !max-w-none" x-data="{ compactPlaceholder: window.innerWidth < 640 }" x-on:resize.window="compactPlaceholder = window.innerWidth < 640">
                        @csrf
                        <div class="bella-search-head !border-0">
                            <x-ui.icon name="search" stroke-width="2" class="w-5 h-5 shrink-0 text-gray-400"/>
                            <input
                                id="landing-search"
                                class="bella-search-input"
                                type="search"
                                :placeholder="compactPlaceholder ? @js(__('Search movies...')) : @js(__('Search movies and TV shows...'))"
                                x-ref="searchInput"
                                name="q"
                            />
                            <button type="submit" class="bella-button !min-h-0 !bg-[#E50914] !px-5 !py-3 !text-sm !text-white hover:!bg-[#c40812]">
                                {{ __('Search') }}
                            </button>
                        </div>
                    </form>

                    <div class="mt-10 flex justify-center">
                        <a
                            href="{{ route('index') }}"
                            class="inline-flex items-center justify-center gap-3 rounded-full border border-white/5 bg-[#1a1a1a] px-10 py-3.5 text-sm font-semibold text-white transition duration-300 hover:bg-red-600 hover:text-white hover:shadow-[0_0_30px_rgba(229,9,20,0.3)]"
                        >
                            <span>{{ __('View Full Website') }}</span>
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center justify-center gap-x-2 gap-y-1 text-xs text-gray-500">
                        <span>{{ __('Trending:') }}</span>
                        @forelse(($trendingTags ?? collect()) as $tag)
                            <a href="{{ route($tag->type, $tag->slug) }}" class="cursor-pointer transition duration-200 hover:text-red-500">
                                {{ $tag->title }}@if(!$loop->last),@endif
                            </a>
                        @empty
                            <a href="{{ route('movies') }}" class="cursor-pointer transition duration-200 hover:text-red-500">{{ __('Movies') }},</a>
                            <a href="{{ route('tvshows') }}" class="cursor-pointer transition duration-200 hover:text-red-500">{{ __('TV Shows') }},</a>
                            <a href="{{ route('trending') }}" class="cursor-pointer transition duration-200 hover:text-red-500">{{ __('New & Popular') }}</a>
                        @endforelse
                    </div>
                </div>

                @if(config('settings.landing_body'))
                    <div class="bella-detail-panel bella-landing-body mx-auto mt-10 max-w-3xl text-left">
                        {!! editor_preview(config('settings.landing_body')) !!}
                    </div>
                @endif

                <div class="mx-auto mt-12 flex max-w-3xl flex-wrap items-center justify-center gap-x-6 gap-y-4 text-xs text-gray-500">
                    <div class="inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-white/35">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 01.707.293l5 5A1 1 0 0116 8v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a1 1 0 01.293-.707l5-5A1 1 0 0110 2zm2.5 7a.75.75 0 000-1.5h-5a.75.75 0 000 1.5h5zm0 3a.75.75 0 000-1.5h-5a.75.75 0 000 1.5h5z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ __('4K Quality') }}</span>
                    </div>
                    <div class="inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-white/35">
                            <path d="M3.5 3.75A2.25 2.25 0 015.75 1.5h8.5a2.25 2.25 0 012.25 2.25v12.5a.75.75 0 01-1.28.53L10 11.81l-4.97 4.97a.75.75 0 01-1.28-.53V3.75z"/>
                        </svg>
                        <span>{{ __('Zero Pop-ads') }}</span>
                    </div>
                    <div class="inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-white/35">
                            <path fill-rule="evenodd" d="M2 10a8 8 0 1116 0 8 8 0 01-16 0zm6.22-2.03a.75.75 0 00-1.22.58v3.9a.75.75 0 001.22.58l3.05-1.95a.75.75 0 000-1.26L8.22 7.97z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ __('Instant Play') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
