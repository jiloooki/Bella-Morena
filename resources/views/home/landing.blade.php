@extends('layouts.landing')

@section('content')
    <section class="bella-page-hero min-h-[78vh]">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(229,9,20,0.28),transparent_38%)]"></div>

        <div class="bella-shell">
            <div class="bella-hero-inner max-w-4xl mx-auto text-center">
                <div class="bella-kicker justify-center before:hidden">{{ __('Bella Morena') }}</div>

                <h1 class="bella-hero-title">{{ config('settings.landing_title') }}</h1>

                <p class="bella-hero-description bella-clamp-3 mx-auto">
                    {{ config('settings.landing_description') }}
                </p>

                <div class="mt-8 mx-auto max-w-3xl w-full">
                    <form method="post" class="bella-search-panel !max-w-none">
                        @csrf
                        <div class="bella-search-head !border-0">
                            <x-ui.icon name="search" stroke-width="2" class="w-5 h-5 shrink-0 text-gray-400"/>
                            <input
                                id="landing-search"
                                class="bella-search-input"
                                type="search"
                                placeholder="{{ __('Search movies and TV shows...') }}"
                                x-ref="searchInput"
                                name="q"
                            />
                            <button type="submit" class="bella-button !min-h-0 !py-3 !px-5 !text-sm">
                                {{ __('Search') }}
                            </button>
                        </div>
                    </form>
                </div>

                @if(config('settings.landing_body'))
                    <div class="bella-detail-panel mt-10 text-left">
                        {!! editor_preview(config('settings.landing_body')) !!}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
