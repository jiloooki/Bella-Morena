@props([
    'listing',
    'showReleaseBadge' => false,
])

@php
    $url = route($listing->type, $listing->slug);
    $year = optional($listing->release_date)->translatedFormat('Y');
    $rating = $listing->vote_average ? number_format((float) $listing->vote_average, 1) : null;
    $genre = $listing->genres->first()?->title;
    $releaseBadge = $showReleaseBadge && $listing->release_date ? $listing->release_date->translatedFormat('M j') : null;
@endphp

<article class="bella-card">
    <a href="{{ $url }}" class="bella-card-link">
        {!! picture($listing->imageurl,config('attr.poster.size_x').','.config('attr.poster.size_y'),'bella-card-image',$listing->title,'post') !!}

        @if($releaseBadge)
            <span class="bella-card-badge bella-card-release-badge">{{ $releaseBadge }}</span>
        @endif

        <div class="bella-card-overlay">
            <div class="bella-card-title">{{ $listing->title }}</div>

            <div class="bella-card-overlay-meta">
                @if($year)
                    <span>{{ $year }}</span>
                @endif
                @if($rating)
                    <span>★ {{ $rating }}</span>
                @endif
                <span>{{ $listing->type === 'movie' ? __('Movie') : __('TV Show') }}</span>
            </div>

            <div class="bella-card-actions">
                <div class="bella-card-actions-group">
                    <span class="bella-card-play" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 translate-x-0.5">
                            <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                        </svg>
                    </span>
                    <span class="bella-card-circle" aria-hidden="true">+</span>
                </div>

                @if($listing->quality)
                    <span class="bella-meta-pill">{{ $listing->quality }}</span>
                @endif
            </div>
        </div>
    </a>

    <div class="bella-card-footer">
        <a href="{{ $url }}" class="bella-card-title">{{ $listing->title }}</a>

        <div class="bella-card-subtitle">
            @if($releaseBadge)
                <span>{{ __('Available :date', ['date' => $releaseBadge]) }}</span>
            @elseif($genre)
                <span>{{ $genre }}</span>
            @endif
            @if($listing->runtime)
                <span>{{ __(':time min', ['time' => $listing->runtime]) }}</span>
            @elseif($year && !$releaseBadge)
                <span>{{ $year }}</span>
            @endif
        </div>
    </div>
</article>
