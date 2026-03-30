<div class="pb-10 lg:pb-16" x-data="{filterOpen:@entangle('filterOpen'),loading:@entangle('loading')}">
    <div class="bella-browse-shell">
        @if(config('settings.listing_filter') == 'v2')
            @include('browse.partials.filterv2')
        @else
            @include('browse.partials.filterv1')
        @endif

        @if(config('settings.listing_genre') == 'active')
            @include('browse.partials.genre')
        @endif

        @if(isset($tmdbResults) && count($tmdbResults) > 0)
            <section class="bella-row mb-8">
                <div class="bella-row-header">
                    <h3 class="bella-row-title">{{ __('More results from TMDB') }}</h3>
                    <span class="bella-meta-pill">{{ count($tmdbResults) }}</span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 2xl:grid-cols-7 gap-5">
                    @foreach($tmdbResults as $tmdb)
                        <article class="bella-card">
                            <a href="{{ route($tmdb['type'] === 'movie' ? 'movie' : 'tv', 'tmdb-' . $tmdb['tmdb_id']) }}" class="bella-card-link">
                                @if($tmdb['image'])
                                    <img src="{{ $tmdb['image'] }}" class="bella-card-image" alt="{{ $tmdb['title'] }}" loading="lazy">
                                @endif

                                <div class="bella-card-overlay">
                                    <div class="bella-card-title">{{ $tmdb['title'] }}</div>
                                    <div class="bella-card-overlay-meta">
                                        @if($tmdb['release_date'])
                                            <span>{{ substr($tmdb['release_date'], 0, 4) }}</span>
                                        @endif
                                        <span>★ {{ $tmdb['vote_average'] }}</span>
                                        <span>TMDB</span>
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
                                    </div>
                                </div>
                            </a>

                            <div class="bella-card-footer">
                                <a href="{{ route($tmdb['type'] === 'movie' ? 'movie' : 'tv', 'tmdb-' . $tmdb['tmdb_id']) }}" class="bella-card-title">{{ $tmdb['title'] }}</a>
                                <div class="bella-card-subtitle">
                                    <span>TMDB</span>
                                    <span>{{ $tmdb['type'] === 'movie' ? __('Movie') : __('TV Show') }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 2xl:grid-cols-7 gap-5">
            @for ($i = 1; $i <= 8; $i++)
                <div class="bella-card animate-pulse" wire:loading wire:target="filter">
                    <div class="aspect-[2/3] rounded-[1.15rem] bg-white/5"></div>
                    <div class="mt-4 space-y-2">
                        <div class="h-4 rounded-full bg-white/5"></div>
                        <div class="h-3 w-2/3 rounded-full bg-white/5"></div>
                    </div>
                </div>
            @endfor
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 2xl:grid-cols-7 gap-5" wire:loading.remove wire:target="filter">
            @foreach($listings as $listing)
                <x-ui.post :listing="$listing"/>

                @if($loop->index == 6)
                    <div class="col-span-full">
                        @include('partials.ads',['id'=> 3])
                    </div>
                @endif
            @endforeach
        </div>

        <div class="bella-pagination">
            {{ $listings->links() }}
        </div>
    </div>
</div>
