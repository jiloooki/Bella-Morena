@extends('layouts.app')

@section('content')
    @php
    @endphp
    <div x-data="{ trailerOpen: false, iframeSrc: '', downloadOpen: false }">
        <section class="bella-detail-hero" id="details">
            <img src="{{ $listing->coverurl ?: $listing->imageurl }}" alt="{{ $episode->name }}" class="bella-detail-backdrop">

            <div class="bella-detail-shell">
                <div class="bella-detail-hero-inner">
                    <div class="bella-kicker">{{ __('Episode') }}</div>
                    <p class="text-sm uppercase tracking-[0.3em] text-gray-300 mb-3">
                        <a href="{{ route('tv', $listing->slug) }}" class="hover:text-white">{{ $listing->title }}</a>
                    </p>

                    <h1 class="bella-detail-title">{{ $episode->name }}</h1>

                    <div class="bella-detail-meta">
                        <span class="bella-meta-pill is-strong">{{ __('S:season E:episode', ['season' => $episode->season_number, 'episode' => $episode->episode_number]) }}</span>
                        @if($episode->air_date)
                            <span class="bella-meta-pill">{{ $episode->air_date->translatedFormat('Y') }}</span>
                        @elseif($listing->release_date)
                            <span class="bella-meta-pill">{{ $listing->release_date->translatedFormat('Y') }}</span>
                        @endif
                        @if($listing->vote_average)
                            <span class="bella-meta-pill">★ {{ number_format((float) $listing->vote_average, 1) }}</span>
                        @endif
                        @if($episode->runtime)
                            <span class="bella-meta-pill">{{ __(':time min', ['time' => $episode->runtime]) }}</span>
                        @elseif($listing->runtime)
                            <span class="bella-meta-pill">{{ __(':time min', ['time' => $listing->runtime]) }}</span>
                        @endif
                    </div>

                    <p class="bella-detail-description bella-clamp-3">
                        {{ $episode->overview ?: $listing->overview }}
                    </p>

                    <div class="bella-detail-actions">
                        <a href="#player" class="bella-button">
                            <span>▶</span>
                            <span>{{ __('Play') }}</span>
                        </a>

                        <livewire:watchlist-component :model="$listing"/>
                        <livewire:reaction-component :model="$episode"/>
                        <livewire:report-component :model="$episode"/>

                        @if($listing->trailer)
                            <button class="bella-button-secondary bella-hide-mobile" @click="trailerOpen = true; iframeSrc = '{{ $listing->trailer }}'">
                                <span>ⓘ</span>
                                <span>{{ __('Trailer') }}</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <div class="bella-detail-content">
            <div class="bella-detail-shell bella-section-stack">
                <div id="player">
                    <livewire:watch-component :listing="$episode"/>
                </div>

                @if(isset($listing->arguments->information))
                    <div class="bella-detail-panel !border-red-500/40 !bg-red-500/10">
                        <div class="flex items-start gap-4">
                            <x-ui.icon name="info" class="w-6 h-6 text-red-300 shrink-0" fill="currentColor"/>
                            <p class="text-red-100">{{ $listing->arguments->information }}</p>
                        </div>
                    </div>
                @endif

                <div class="bella-detail-grid">
                    <div class="bella-detail-panel">
                        <div class="bella-detail-summary">
                            <div class="bella-detail-summary-top">
                                <div class="bella-detail-thumb">
                                    <img src="{{ $listing->imageurl }}" alt="{{ $listing->title }}" class="w-full h-full object-cover">
                                </div>

                                <div class="bella-detail-summary-main">
                                    <div class="bella-detail-summary-head">
                                        <div class="bella-detail-summary-copy">
                                            <p class="bella-detail-summary-kicker">{{ $listing->title }}</p>
                                            <h2 class="bella-detail-summary-title">{{ $episode->name }}</h2>
                                            <div class="bella-detail-summary-meta">
                                                <span>{{ __('S:season E:episode', ['season' => $episode->season_number, 'episode' => $episode->episode_number]) }}</span>
                                                @if($episode->air_date)
                                                    <span>{{ $episode->air_date->translatedFormat('Y') }}</span>
                                                @endif
                                                @if($episode->runtime)
                                                    <span>{{ __(':time min', ['time' => $episode->runtime]) }}</span>
                                                @elseif($listing->runtime)
                                                    <span>{{ __(':time min', ['time' => $listing->runtime]) }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="bella-detail-summary-actions">
                                            @if($listing->trailer)
                                                <button type="button" class="bella-button-secondary bella-detail-secondary-button" @click="trailerOpen = true; iframeSrc = '{{ $listing->trailer }}'">
                                                    {{ __('Watch trailer') }}
                                                </button>
                                            @endif

                                            @if(count($episode->downloads) > 0)
                                                <button type="button" class="bella-button bella-detail-secondary-button" @click="downloadOpen = true">
                                                    {{ __('Download') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <p class="bella-detail-description bella-detail-copy !max-w-none">{{ $episode->overview ?: $listing->overview }}</p>

                                    <div class="bella-info-grid bella-detail-meta-list">
                                        <div class="bella-info-row">
                                            <div class="bella-info-label">{{ __('Series') }}</div>
                                            <div><a href="{{ route('tv', $listing->slug) }}" class="hover:underline">{{ $listing->title }}</a></div>
                                        </div>

                                        <div class="bella-info-row">
                                            <div class="bella-info-label">{{ __('Episode') }}</div>
                                            <div>{{ __('Season :season, Episode :episode', ['season' => $episode->season_number, 'episode' => $episode->episode_number]) }}</div>
                                        </div>

                                        @if($episode->air_date)
                                            <div class="bella-info-row">
                                                <div class="bella-info-label">{{ __('Aired') }}</div>
                                                <div>{{ $episode->air_date->translatedFormat('d M, Y') }}</div>
                                            </div>
                                        @endif

                                        @if($listing->country_id)
                                            <div class="bella-info-row">
                                                <div class="bella-info-label">{{ __('Country') }}</div>
                                                <div>
                                                    <a href="{{ route('country',['country'=> $listing->country->slug]) }}" class="hover:underline">{{ $listing->country->name }}</a>
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

                                    <div class="bella-tag-list">
                                        @include('watch.partials.tag')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <livewire:season-component :model="$listing" type="episode" :selectEpisode="$episode->episode_number" :seasonId="$episode->season->id"/>
                    </div>
                </div>

                @include('partials.ads',['id'=> 1])
            </div>

            <livewire:comments :model="$episode"/>

            <section class="bella-row">
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
