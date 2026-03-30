@props(['listing'])

@php
    $url = route('episode', ['slug' => $listing->post->slug, 'season' => $listing->season_number, 'episode' => $listing->episode_number]);
@endphp

<article class="bella-card is-episode">
    <a href="{{ $url }}" class="bella-card-link">
        <img src="{{ $listing->imageurl }}" alt="{{ $listing->name ?? $listing->post->title }}" class="bella-card-image">

        <div class="bella-card-overlay">
            <div class="bella-card-title">{{ $listing->name ?? $listing->post->title }}</div>

            <div class="bella-card-overlay-meta">
                <span>{{ __('Season') }} {{ $listing->season_number }}</span>
                <span>{{ __('Episode') }} {{ $listing->episode_number }}</span>
                @if($listing->air_date)
                    <span>{{ $listing->air_date->translatedFormat('Y') }}</span>
                @endif
            </div>

            <div class="bella-card-actions">
                <div class="bella-card-actions-group">
                    <span class="bella-card-play" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 translate-x-0.5">
                            <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                        </svg>
                    </span>
                </div>
            </div>
        </div>
    </a>

    <div class="bella-card-footer">
        <a href="{{ $url }}" class="bella-card-title">{{ $listing->name ?? $listing->post->title }}</a>
        <div class="bella-card-subtitle">
            <span>{{ __('Season') }} {{ $listing->season_number }}</span>
            <span>{{ __('Episode') }} {{ $listing->episode_number }}</span>
        </div>
    </div>
</article>
