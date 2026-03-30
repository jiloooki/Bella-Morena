@extends('layouts.app')

@section('content')
    @php
        $slider = collect($listings['slider'] ?? []);
        $featured = collect($listings['featured'] ?? []);
        $movies = collect($listings['movie'] ?? []);
        $tvShows = collect($listings['tv'] ?? []);
        $comingSoon = collect($listings['comingSoon'] ?? []);
        $hero = $slider->first() ?? $featured->first() ?? $movies->first() ?? $tvShows->first();

        $mixed = $slider
            ->merge($featured)
            ->merge($movies)
            ->merge($tvShows)
            ->filter()
            ->unique('id')
            ->values();

        $newReleases = $mixed
            ->filter(fn ($item) => $item->release_date)
            ->sortByDesc(fn ($item) => optional($item->release_date)->timestamp ?? 0)
            ->values()
            ->take(12);

        $topRated = $mixed
            ->sortByDesc(fn ($item) => (float) $item->vote_average)
            ->values()
            ->take(12);

        $rows = collect([
            [
                'title' => __('Trending Now'),
                'listings' => $featured->isNotEmpty() ? $featured : $mixed->take(12),
                'seeAll' => route('trending'),
            ],
            [
                'title' => __('Popular Movies'),
                'listings' => $movies,
                'seeAll' => route('movies'),
            ],
            [
                'title' => __('Popular TV Shows'),
                'listings' => $tvShows,
                'seeAll' => route('tvshows'),
            ],
            [
                'title' => __('Coming Soon'),
                'listings' => $comingSoon,
                'seeAll' => null,
                'variant' => 'coming-soon',
            ],
            [
                'title' => __('New Releases'),
                'listings' => $newReleases,
                'seeAll' => route('browse'),
            ],
            [
                'title' => __('Top Rated'),
                'listings' => $topRated,
                'seeAll' => route('topimdb'),
            ],
        ])->filter(fn ($row) => collect($row['listings'])->isNotEmpty());
    @endphp

    <div class="bella-page bella-homepage">
        @if($hero)
            <section class="bella-page-hero bella-home-hero">
                <img src="{{ $hero->coverurl ?: $hero->imageurl }}" alt="{{ $hero->title }}" class="bella-hero-backdrop">

                <div class="bella-shell">
                    <div class="bella-hero-inner">
                        <div class="bella-kicker">{{ $hero->type === 'movie' ? __('Featured Movie') : __('Featured Series') }}</div>

                        <h1 class="bella-hero-title">{{ $hero->title }}</h1>

                        <div class="bella-hero-meta">
                            @if($hero->vote_average)
                                <span class="bella-meta-pill is-strong">★ {{ number_format((float) $hero->vote_average, 1) }}</span>
                            @endif
                            @if($hero->release_date)
                                <span class="bella-meta-pill">{{ $hero->release_date->translatedFormat('Y') }}</span>
                            @endif
                            @if($hero->runtime)
                                <span class="bella-meta-pill">{{ __(':time min', ['time' => $hero->runtime]) }}</span>
                            @else
                                <span class="bella-meta-pill">{{ $hero->type === 'movie' ? __('Movie') : __('TV Show') }}</span>
                            @endif
                        </div>

                        <p class="bella-hero-description bella-clamp-2">
                            {{ $hero->overview ?: __('Stream the most popular stories, curated in a cinematic Bella Morena experience.') }}
                        </p>

                        <div class="bella-hero-actions">
                            <a href="{{ route($hero->type, $hero->slug) }}" class="bella-button">
                                <span>▶</span>
                                <span>{{ __('Play') }}</span>
                            </a>
                            <a href="{{ route($hero->type, $hero->slug) }}#details" class="bella-button-secondary">
                                <span>ⓘ</span>
                                <span>{{ __('More Info') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <div class="bella-row-section bella-home-rows">
            @foreach($rows as $row)
                <x-ui.home-list
                    :listings="$row['listings']"
                    :heading="$row['title']"
                    :seeAll="$row['seeAll']"
                    :variant="$row['variant'] ?? null"
                    card="post"
                />
            @endforeach

            @if(config('settings.footer_description'))
                <div class="bella-row-shell">
                    <div class="bella-detail-panel">
                        {!! editor_preview(config('settings.footer_description')) !!}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
