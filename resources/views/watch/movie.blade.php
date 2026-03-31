@extends('layouts.app')

@section('content')
    @php
        $isComingSoon = $listing->release_date && $listing->release_date->gt(now()->startOfDay());
    @endphp

    <div
        x-data="{
            trailerOpen: false,
            iframeSrc: '',
            downloadOpen: false,
            async sharePage() {
                const payload = {
                    title: @js($listing->title),
                    url: window.location.href
                };

                try {
                    if (navigator.share) {
                        await navigator.share(payload);
                    } else if (navigator.clipboard) {
                        await navigator.clipboard.writeText(payload.url);
                    }
                } catch (error) {
                }
            }
        }"
    >
        <section class="bella-detail-hero" id="details">
            <img src="{{ $listing->coverurl ?: $listing->imageurl }}" alt="{{ $listing->title }}" class="bella-detail-backdrop">

            <div class="bella-detail-shell">
                <div class="bella-detail-hero-inner bella-movie-hero-inner">
                    @unless($isComingSoon)
                        <div class="bella-kicker">{{ __('Movie') }}</div>

                        @if(config('settings.show_titlesub') == 'active' && $listing->title_sub)
                            <p class="text-sm uppercase tracking-[0.3em] text-gray-300 mb-3">{{ $listing->title_sub }}</p>
                        @endif

                        <h1 class="bella-detail-title">{{ $listing->title }}</h1>

                        <div class="bella-detail-meta">
                            @if($listing->quality)
                                <span class="bella-meta-pill is-strong">{{ $listing->quality }}</span>
                            @endif
                            @if($listing->release_date)
                                <span class="bella-meta-pill">{{ $listing->release_date->translatedFormat('Y') }}</span>
                            @endif
                            @if($listing->vote_average)
                                <span class="bella-meta-pill">★ {{ number_format((float) $listing->vote_average, 1) }}</span>
                            @endif
                            @if($listing->runtime)
                                <span class="bella-meta-pill">{{ __(':time min', ['time' => $listing->runtime]) }}</span>
                            @endif
                            @foreach($listing->genres->take(2) as $genre)
                                <span class="bella-meta-pill">{{ $genre->title }}</span>
                            @endforeach
                        </div>

                        <p class="bella-detail-description bella-clamp-2">
                            {{ $listing->overview }}
                        </p>

                        <div class="bella-detail-action-rail bella-movie-action-rail">
                            <div class="bella-detail-actions-primary bella-movie-primary-actions">
                                <a href="#player" class="bella-button">
                                    <span>▶</span>
                                    <span>{{ __('Play') }}</span>
                                </a>

                                <livewire:watchlist-component
                                    :model="$listing"
                                    showTextAlways="true"
                                    buttonClass="bella-movie-primary-button"
                                />
                            </div>

                            <div class="bella-detail-subactions bella-movie-secondary-actions">
                                <livewire:reaction-component
                                    :model="$listing"
                                    hideLikeLabel="true"
                                    wrapperClass="bella-detail-subaction-group bella-movie-secondary-row"
                                    buttonClass="bella-detail-subaction-button bella-movie-icon-button"
                                />

                                <livewire:report-component
                                    :model="$listing"
                                    buttonClass="bella-detail-subaction-button bella-movie-report-button"
                                    showDesktopLabel="true"
                                />

                                <button
                                    type="button"
                                    class="bella-detail-subaction-button bella-movie-share-button"
                                    @click="sharePage()"
                                    aria-label="{{ __('Share') }}"
                                >
                                    <x-ui.icon name="link" class="w-4 h-4 shrink-0" fill="currentColor"/>
                                    <span class="bella-detail-subaction-label hidden md:inline">{{ __('Share') }}</span>
                                </button>

                                @if($listing->trailer)
                                    <button
                                        type="button"
                                        class="bella-detail-subaction-button bella-movie-trailer-button bella-hide-mobile"
                                        @click="trailerOpen = true; iframeSrc = '{{ $listing->trailer }}'"
                                    >
                                        <x-ui.icon name="movie" class="w-4 h-4 shrink-0" stroke="currentColor"/>
                                        <span class="bella-detail-subaction-label">{{ __('Trailer') }}</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endunless
                </div>
            </div>
        </section>

        <div class="bella-detail-content bella-movie-detail-content">
            <div class="bella-detail-shell bella-section-stack">
                @if($isComingSoon)
                    <div id="coming-soon">
                        @include('watch.partials.coming-soon', ['listing' => $listing])
                    </div>
                @else
                    <div id="player">
                        <livewire:watch-component :listing="$listing"/>
                    </div>
                @endif

                @if(isset($listing->arguments->information))
                    <div class="bella-detail-panel !border-red-500/40 !bg-red-500/10">
                        <div class="flex items-start gap-4">
                            <x-ui.icon name="info" class="w-6 h-6 text-red-300 shrink-0" fill="currentColor"/>
                            <p class="text-red-100">{{ $listing->arguments->information }}</p>
                        </div>
                    </div>
                @endif

                <div class="bella-detail-panel bella-movie-details-panel">
                    <div class="bella-movie-details-stack">
                        <div>
                            <h3 class="bella-section-heading !mb-0">{{ __('Details') }}</h3>
                        </div>

                        <div class="bella-info-grid bella-detail-meta-list bella-movie-info-grid">
                            @if($listing->country_id)
                                <div class="bella-info-row">
                                    <div class="bella-info-label">{{ __('Country') }}</div>
                                    <div>
                                        <a href="{{ route('country',['country' => $listing->country->slug]) }}" class="hover:underline">{{ $listing->country->name }}</a>
                                    </div>
                                </div>
                            @endif

                            @if(count($listing->genres) > 0)
                                <div class="bella-info-row">
                                    <div class="bella-info-label">{{ __('Genre') }}</div>
                                    <div>
                                        @foreach($listing->genres as $genre)
                                            <a href="{{ route('genre',['genre' => $genre->slug]) }}" class="hover:underline not-last-child-after inline-block mr-1 after:content-[','] last:mr-0 last:after:hidden">{{ $genre->title }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($listing->release_date)
                                <div class="bella-info-row">
                                    <div class="bella-info-label">{{ __('Released') }}</div>
                                    <div>{{ $listing->release_date->translatedFormat('d M, Y') }}</div>
                                </div>
                            @endif

                            @if(count($listing->peoples) > 0)
                                <div class="bella-info-row">
                                    <div class="bella-info-label">{{ __('Cast') }}</div>
                                    <div>
                                        @foreach($listing->peoples as $people)
                                            <a href="{{ route('people',['slug'=> $people->slug]) }}" class="hover:underline not-last-child-after inline-block mr-1 after:content-[','] last:mr-0 last:after:hidden">{{ $people->name }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="bella-tag-list bella-movie-tag-list">
                            @include('watch.partials.tag')
                        </div>
                    </div>
                </div>

                @include('partials.ads',['id'=> 1])
            </div>

            <livewire:comments :model="$listing"/>

            <section class="bella-row bella-movie-related-row">
                <div class="bella-row-shell">
                    <div class="bella-row-header">
                        <h3 class="bella-row-title">{{ __('More Like This') }}</h3>
                    </div>

                    <div class="bella-row-track">
                        @foreach($recommends as $recommend)
                            <x-ui.post :listing="$recommend"/>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>

        <div class="fixed inset-0 z-50 bella-modal-overlay" x-show="trailerOpen" style="display: none;"></div>
        <div
            class="fixed inset-0 z-50 overflow-hidden flex items-start top-20 justify-center px-4 sm:px-6"
            role="dialog"
            aria-modal="true"
            x-show="trailerOpen"
            x-transition:enter="transition ease-in-out duration-200"
            x-transition:enter-start="opacity-0 trangray-y-4"
            x-transition:enter-end="opacity-100 trangray-y-0"
            x-transition:leave="transition ease-in-out duration-200"
            x-transition:leave-start="opacity-100 trangray-y-0"
            x-transition:leave-end="opacity-0 trangray-y-4"
            style="display: none;"
        >
            <div class="bella-modal-card overflow-auto max-w-6xl w-full rounded-xl"
                 @click.outside="trailerOpen = false"
                 @keydown.escape.window="trailerOpen = false">
                <iframe :src="iframeSrc" title="Trailer embed" frameborder="0" class="w-full aspect-video"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen></iframe>
            </div>
        </div>

        <div class="fixed inset-0 z-50 bella-modal-overlay" x-show="downloadOpen" style="display: none;"></div>
        <div
            class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center px-4 sm:px-6"
            role="dialog"
            aria-modal="true"
            x-show="downloadOpen"
            x-transition:enter="transition ease-in-out duration-200"
            x-transition:enter-start="opacity-0 trangray-y-4"
            x-transition:enter-end="opacity-100 trangray-y-0"
            x-transition:leave="transition ease-in-out duration-200"
            x-transition:leave-start="opacity-100 trangray-y-0"
            x-transition:leave-end="opacity-0 trangray-y-4"
            style="display: none;"
        >
            <div class="bella-modal-card max-w-xl w-full rounded-xl p-6 lg:p-8"
                 @click.outside="downloadOpen = false"
                 @keydown.escape.window="downloadOpen = false">
                <h3 class="bella-section-heading text-center">{{ __('Download Link') }}</h3>

                <ul class="flex flex-col divide-y divide-white/10 max-h-[60vh] overflow-auto -mr-4 pr-4 scrollbar-thumb-gray-700 scrollbar-track-transparent scrollbar-rounded-lg scrollbar-thin">
                    @foreach($listing->downloads as $download)
                        <li class="inline-flex items-center justify-between gap-x-4 py-4 font-medium text-white">
                            <div>{{ $download->label }}</div>
                            <x-form.primary href="{{ $download->link }}" target="_blank" class="px-5 gap-2 !py-2.5 !rounded-full !bg-[#E50914] !border-[#E50914]">
                                <span class="font-normal">{{ __('Download') }}</span>
                                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M0 0h24v24H0V0z" fill="none"></path>
                                    <path d="M16.59 9H15V4c0-.55-.45-1-1-1h-4c-.55 0-1 .45-1 1v5H7.41c-.89 0-1.34 1.08-.71 1.71l4.59 4.59c.39.39 1.02.39 1.41 0l4.59-4.59c.63-.63.19-1.71-.7-1.71zM5 19c0 .55.45 1 1 1h12c.55 0 1-.45 1-1s-.45-1-1-1H6c-.55 0-1 .45-1 1z"></path>
                                </svg>
                            </x-form.primary>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection
