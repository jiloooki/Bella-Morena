@extends('layouts.app')

@section('content')
    @php
        $isComingSoon = $listing->release_date && $listing->release_date->gt(now()->startOfDay());
    @endphp
    <div x-data="{ trailerOpen: false, iframeSrc: '' }">
        <section class="bella-detail-hero" id="details">
            <img src="{{ $listing->coverurl ?: $listing->imageurl }}" alt="{{ $listing->title }}" class="bella-detail-backdrop">

            <div class="bella-detail-shell">
                <div class="bella-detail-hero-inner">
                    <div class="bella-kicker">{{ __('TV Show') }}</div>

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
                        <span class="bella-meta-pill">{{ __(':count seasons', ['count' => $listing->seasons_count]) }}</span>
                        @foreach($listing->genres->take(2) as $genre)
                            <span class="bella-meta-pill">{{ $genre->title }}</span>
                        @endforeach
                    </div>

                    <p class="bella-detail-description bella-clamp-2">
                        {{ $listing->overview }}
                    </p>

                    <div class="bella-detail-actions">
                        @if($isComingSoon)
                            <a href="#coming-soon" class="bella-button-secondary">
                                <span>⏳</span>
                                <span>{{ __('Coming Soon') }}</span>
                            </a>
                        @elseif($listing->seasons_count > 0)
                            <a href="#episodes" class="bella-button">
                                <span>▶</span>
                                <span>{{ __('Browse Episodes') }}</span>
                            </a>
                        @endif

                        <livewire:watchlist-component :model="$listing"/>
                        <livewire:reaction-component :model="$listing"/>
                        <livewire:report-component :model="$listing"/>

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

        <div class="bella-detail-content bella-tv-detail-content">
            <div class="bella-detail-shell bella-section-stack">
                @if($isComingSoon)
                    @include('watch.partials.coming-soon', ['listing' => $listing])
                @endif

                @if(isset($listing->arguments->information))
                    <div class="bella-detail-panel !border-red-500/40 !bg-red-500/10">
                        <div class="flex items-start gap-4">
                            <x-ui.icon name="info" class="w-6 h-6 text-red-300 shrink-0" fill="currentColor"/>
                            <p class="text-red-100">{{ $listing->arguments->information }}</p>
                        </div>
                    </div>
                @endif

                <div class="bella-tv-detail-grid">
                    <div class="bella-tv-main-column">
                        <div class="bella-detail-panel bella-tv-info-panel">
                            <div class="bella-detail-summary">
                                <div class="bella-detail-summary-top">
                                    <div class="bella-detail-thumb">
                                        <img src="{{ $listing->imageurl }}" alt="{{ $listing->title }}" class="w-full h-full object-cover">
                                    </div>

                                    <div class="bella-detail-summary-main">
                                        <div class="bella-detail-summary-head">
                                            <div class="bella-detail-summary-copy">
                                                <h2 class="bella-detail-summary-title">{{ $listing->title }}</h2>
                                                <div class="bella-detail-summary-meta">
                                                    @if($listing->release_date)
                                                        <span>{{ $listing->release_date->translatedFormat('Y') }}</span>
                                                    @endif
                                                    @if($listing->runtime)
                                                        <span>{{ __(':time min', ['time' => $listing->runtime]) }}</span>
                                                    @endif
                                                    <span>{{ __(':count seasons', ['count' => $listing->seasons_count]) }}</span>
                                                </div>
                                            </div>

                                            <div class="bella-detail-summary-actions">
                                                @if($listing->trailer)
                                                    <button type="button" class="bella-button-secondary bella-detail-secondary-button" @click="trailerOpen = true; iframeSrc = '{{ $listing->trailer }}'">
                                                        {{ __('Watch trailer') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </div>

                                        <p class="bella-detail-description bella-detail-copy !max-w-none">{{ $listing->overview }}</p>

                                        <div class="bella-info-grid bella-detail-meta-list">
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

                                        <div class="bella-tag-list">
                                            @include('watch.partials.tag')
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @include('partials.ads',['id'=> 1])
                    </div>

                    @if($listing->seasons_count > 0)
                        <aside id="episodes" class="bella-tv-sidebar">
                            <livewire:season-component :model="$listing" type="tv"/>
                        </aside>
                    @endif
                </div>
            </div>

            <livewire:comments :model="$listing"/>

            <section class="bella-row bella-tv-related-row">
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
    </div>
@endsection
