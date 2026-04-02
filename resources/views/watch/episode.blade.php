@extends('layouts.app')

@section('content')
    @php
        $episodeDescription = $episode->overview ?: $listing->overview;
        $episodeRuntime = $episode->runtime ?: $listing->runtime;
        $shareTitle = trim($listing->title.' - '.$episode->name);
        $heroEpisodeLabel = sprintf('S%02d E%02d', $episode->season_number, $episode->episode_number);
    @endphp

    <div
        class="bella-page bella-immersive-page bella-detail-page"
        x-data="{
            trailerOpen: false,
            iframeSrc: '',
            downloadOpen: false,
            async sharePage() {
                const payload = {
                    title: @js($shareTitle),
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
            <img src="{{ $listing->coverurl ?: $listing->imageurl }}" alt="{{ $episode->name }}" class="bella-detail-backdrop">

            <div class="bella-detail-shell">
                <div class="bella-detail-hero-inner bella-movie-hero-inner bella-episode-hero-inner">
                    <div class="bella-kicker">{{ $heroEpisodeLabel }}</div>

                    <p class="text-sm uppercase tracking-[0.3em] text-gray-300 mb-3">
                        <a href="{{ route('tv', $listing->slug) }}" class="hover:text-white">{{ $listing->title }}</a>
                    </p>

                    <h1 class="bella-detail-title">{{ $episode->name }}</h1>

                    <div class="bella-detail-meta">
                        @if($listing->vote_average)
                            <span class="bella-meta-pill is-strong">★ {{ number_format((float) $listing->vote_average, 1) }}</span>
                        @endif
                        <span class="bella-meta-pill {{ !$listing->vote_average ? 'is-strong' : '' }}">{{ __('S:season E:episode', ['season' => $episode->season_number, 'episode' => $episode->episode_number]) }}</span>
                        @if($episode->air_date)
                            <span class="bella-meta-pill">{{ $episode->air_date->translatedFormat('Y') }}</span>
                        @elseif($listing->release_date)
                            <span class="bella-meta-pill">{{ $listing->release_date->translatedFormat('Y') }}</span>
                        @endif
                        @if($episodeRuntime)
                            <span class="bella-meta-pill">{{ __(':time min', ['time' => $episodeRuntime]) }}</span>
                        @endif
                        @foreach($listing->genres->take(2) as $genre)
                            <span class="bella-meta-pill">{{ $genre->title }}</span>
                        @endforeach
                    </div>

                    <p class="bella-detail-description bella-clamp-2">
                        {{ $episodeDescription }}
                    </p>

                    <div class="bella-detail-action-rail bella-movie-action-rail bella-episode-action-rail">
                        <div class="bella-detail-actions-primary bella-movie-primary-actions">
                            @if($previousEpisode)
                                <a
                                    href="{{ route('episode', ['slug' => $listing->slug, 'season' => $previousEpisode->season_number, 'episode' => $previousEpisode->episode_number]) }}"
                                    class="bella-inline-action bella-movie-primary-button bella-episode-nav-button is-secondary"
                                >
                                    <span>{{ __('Previous Episode') }}</span>
                                </a>
                            @else
                                <span class="bella-inline-action bella-movie-primary-button bella-episode-nav-button is-secondary is-disabled">
                                    <span>{{ __('Previous Episode') }}</span>
                                </span>
                            @endif

                            @if($nextEpisode)
                                <a
                                    href="{{ route('episode', ['slug' => $listing->slug, 'season' => $nextEpisode->season_number, 'episode' => $nextEpisode->episode_number]) }}"
                                    class="bella-button bella-episode-nav-button"
                                >
                                    <span>{{ __('Next Episode') }}</span>
                                </a>
                            @else
                                <span class="bella-button bella-episode-nav-button is-disabled">
                                    <span>{{ __('Next Episode') }}</span>
                                </span>
                            @endif

                            <livewire:watchlist-component
                                :model="$listing"
                                showTextAlways="true"
                                buttonClass="bella-movie-primary-button bella-episode-watchlist-button"
                            />
                        </div>

                        <div class="bella-detail-subactions bella-movie-secondary-actions">
                            <livewire:reaction-component
                                :model="$episode"
                                hideLikeLabel="true"
                                wrapperClass="bella-detail-subaction-group bella-movie-secondary-row"
                                buttonClass="bella-detail-subaction-button bella-movie-icon-button"
                            />

                            <livewire:report-component
                                :model="$episode"
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
                                <span class="bella-detail-subaction-label bella-movie-secondary-label">{{ __('Share') }}</span>
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
                </div>
            </div>
        </section>

        <div class="bella-detail-content bella-episode-detail-content">
            <div class="bella-detail-shell bella-section-stack">
                <section id="player" class="bella-episode-player-shell">
                    <livewire:watch-component :listing="$episode"/>
                </section>

                @if(isset($listing->arguments->information))
                    <div class="bella-detail-panel !border-red-500/40 !bg-red-500/10">
                        <div class="flex items-start gap-4">
                            <x-ui.icon name="info" class="w-6 h-6 text-red-300 shrink-0" fill="currentColor"/>
                            <p class="text-red-100">{{ $listing->arguments->information }}</p>
                        </div>
                    </div>
                @endif

                <div class="bella-episode-layout-grid">
                    <aside class="bella-episode-sidebar-column">
                        <livewire:season-component :model="$listing" type="episode" :currentEpisodeId="$episode->id" :seasonId="$episode->season->id"/>
                    </aside>

                    <div class="bella-episode-main-column">
                        <div class="bella-detail-panel bella-episode-details-panel">
                            <div class="bella-episode-details-header">
                                <div>
                                    <p class="bella-detail-summary-kicker">{{ __('Now Watching') }}</p>
                                    <h2 class="bella-section-heading !mb-1">{{ __('About this episode') }}</h2>
                                    <p class="text-sm text-gray-400">
                                        {{ __('Season :season, Episode :episode of :title', ['season' => $episode->season_number, 'episode' => $episode->episode_number, 'title' => $listing->title]) }}
                                    </p>
                                </div>

                                @if(count($episode->downloads) > 0)
                                    <button type="button" class="bella-button-secondary bella-detail-secondary-button bella-episode-download-button" @click="downloadOpen = true">
                                        {{ __('Download') }}
                                    </button>
                                @endif
                            </div>

                            @if($episodeDescription)
                                <div
                                    class="bella-episode-description-block"
                                    x-data="{
                                        expanded: false,
                                        canExpand: false,
                                        measure() {
                                            const el = this.$refs.description;

                                            if (!el) {
                                                this.canExpand = false;
                                                return;
                                            }

                                            this.canExpand = el.scrollHeight > el.clientHeight + 2;
                                        }
                                    }"
                                    x-init="$nextTick(() => measure())"
                                    @resize.window.debounce.150ms="measure()"
                                >
                                    <p
                                        x-ref="description"
                                        class="bella-detail-description bella-detail-copy bella-episode-copy !max-w-none"
                                        :class="{ 'line-clamp-3': !expanded }"
                                    >
                                        {{ $episodeDescription }}
                                    </p>

                                    <button
                                        type="button"
                                        class="bella-episode-read-more"
                                        x-show="canExpand"
                                        x-cloak
                                        @click="expanded = !expanded"
                                    >
                                        <span x-text="expanded ? @js(__('Read Less')) : @js(__('Read More'))"></span>
                                    </button>
                                </div>
                            @endif

                            <div class="bella-episode-facts-grid">
                                <div class="bella-episode-fact">
                                    <div class="bella-episode-fact-label">{{ __('Series') }}</div>
                                    <div class="bella-episode-fact-value">
                                        <a href="{{ route('tv', $listing->slug) }}" class="hover:underline">{{ $listing->title }}</a>
                                    </div>
                                </div>

                                @if($episode->air_date)
                                    <div class="bella-episode-fact">
                                        <div class="bella-episode-fact-label">{{ __('Aired') }}</div>
                                        <div class="bella-episode-fact-value">{{ $episode->air_date->translatedFormat('d M, Y') }}</div>
                                    </div>
                                @endif

                                @if($episodeRuntime)
                                    <div class="bella-episode-fact">
                                        <div class="bella-episode-fact-label">{{ __('Runtime') }}</div>
                                        <div class="bella-episode-fact-value">{{ __(':time min', ['time' => $episodeRuntime]) }}</div>
                                    </div>
                                @endif

                                @if($listing->country_id)
                                    <div class="bella-episode-fact">
                                        <div class="bella-episode-fact-label">{{ __('Country') }}</div>
                                        <div class="bella-episode-fact-value">
                                            <a href="{{ route('country', ['country' => $listing->country->slug]) }}" class="hover:underline">{{ $listing->country->name }}</a>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if(isset($listing->tags) && $listing->tags->count())
                                <div class="bella-episode-tag-block">
                                    <div class="bella-info-label">{{ __('Tags') }}</div>

                                    <div class="bella-tag-list bella-movie-tag-list">
                                        @include('watch.partials.tag')
                                    </div>
                                </div>
                            @endif
                        </div>

                        @include('partials.ads', ['id' => 1])
                    </div>
                </div>
            </div>

            <section class="bella-row bella-tv-related-row bella-episode-related-row">
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

            <livewire:comments :model="$episode"/>
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
                    @foreach($episode->downloads as $download)
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
