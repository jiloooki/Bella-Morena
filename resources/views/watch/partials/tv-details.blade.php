<div class="bella-detail-panel bella-movie-details-panel bella-tv-details-panel">
    <div class="bella-movie-details-stack">
        <div>
            <h3 class="bella-section-heading !mb-0">{{ __('Details') }}</h3>
        </div>

        <div class="bella-info-grid bella-detail-meta-list bella-movie-info-grid">
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

        <div class="bella-tag-list bella-movie-tag-list">
            @include('watch.partials.tag')
        </div>
    </div>
</div>
