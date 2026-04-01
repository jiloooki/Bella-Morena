@extends('layouts.app')

@section('content')
    @php
        $isComingSoon = $listing->release_date && $listing->release_date->gt(now()->startOfDay());
    @endphp
    <div x-data="{ trailerOpen: false, iframeSrc: '' }">
        <section class="bella-detail-hero" id="details">
            <img src="{{ $listing->coverurl ?: $listing->imageurl }}" alt="{{ $listing->title }}" class="bella-detail-backdrop">

            <div class="bella-detail-shell">
                <div class="bella-detail-hero-inner bella-movie-hero-inner">
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

                    <div class="bella-detail-action-rail bella-movie-action-rail">
                        <div class="bella-detail-actions-primary bella-movie-primary-actions">
                            @if(!$isComingSoon && $listing->seasons_count > 0)
                                <a href="#episodes" class="bella-button">
                                    <span>▶</span>
                                    <span>{{ __('Browse Episodes') }}</span>
                                </a>
                            @endif

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

                @if($listing->seasons_count > 0)
                    <div class="bella-tv-layout-grid">
                        <div class="bella-tv-main-column">
                            <section id="episodes">
                                <livewire:season-component :model="$listing" type="tv"/>
                            </section>
                        </div>

                        <aside class="bella-tv-sidebar-column">
                            @include('watch.partials.tv-details', ['listing' => $listing])

                            <div class="bella-tv-sidebar-ad">
                                @include('partials.ads',['id'=> 1])
                            </div>
                        </aside>
                    </div>
                @else
                    @include('watch.partials.tv-details', ['listing' => $listing])
                    @include('partials.ads',['id'=> 1])
                @endif
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
