<div>
    <div class="fixed inset-0 z-50 bella-search-overlay" x-show="searchOpen" style="display: none;"></div>

    <div
        id="search-modal"
        class="fixed inset-0 z-50 overflow-hidden flex items-start justify-center pt-24 px-4 sm:px-6"
        role="dialog"
        aria-modal="true"
        x-show="searchOpen"
        x-transition:enter="transition ease-in-out duration-200"
        x-transition:enter-start="opacity-0 trangray-y-4"
        x-transition:enter-end="opacity-100 trangray-y-0"
        x-transition:leave="transition ease-in-out duration-200"
        x-transition:leave-start="opacity-100 trangray-y-0"
        x-transition:leave-end="opacity-0 trangray-y-4"
        style="display: none;"
    >
        <div class="bella-search-panel w-full"
             @click.outside="searchOpen = false"
             @keydown.escape.window="searchOpen = false">
            <div class="bella-search-head">
                <x-ui.icon name="search" stroke-width="2" class="w-5 h-5 shrink-0 text-gray-400"/>
                <input
                    id="modal-search"
                    class="bella-search-input"
                    type="search"
                    placeholder="{{ __('Search movies, shows, and TMDB titles...') }}"
                    x-ref="searchInput"
                    name="q"
                    wire:model.live.debounce.500ms="q"
                />

                <div role="status" wire:loading>
                    <svg aria-hidden="true" class="inline w-6 h-6 text-gray-200 animate-spin fill-primary-500" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                    </svg>
                    <span class="sr-only">Loading...</span>
                </div>
            </div>

            <div class="bella-search-columns">
                <div class="bella-search-group">
                    <div class="bella-search-group-title">{{ __('In Bella Morena') }}</div>

                    @if(count($posts) > 0)
                        <div class="space-y-2">
                            @foreach($posts as $post)
                                <a class="bella-search-item" href="{{ route($post->type, $post->slug) }}">
                                    <div class="bella-search-thumb">
                                        {!! picture($post->imageurl,config('attr.poster.size_x').','.config('attr.poster.size_y'),'absolute h-full w-full object-cover rounded-md',$post->title,'post') !!}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-white line-clamp-1">{{ $post->title }}</div>
                                        <div class="text-xs text-gray-400 mt-1 line-clamp-2">{{ Str::limit($post->overview, 110) }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="bella-search-empty">{{ __('Search locally to see matching titles.') }}</div>
                    @endif
                </div>

                <div class="bella-search-group">
                    <div class="bella-search-group-title">{{ __('From TMDB') }}</div>

                    @if(count($tmdbResults) > 0)
                        <div class="space-y-2">
                            @foreach($tmdbResults as $tmdb)
                                <a class="bella-search-item" href="{{ route($tmdb['type'] === 'movie' ? 'movie' : 'tv', 'tmdb-' . $tmdb['tmdb_id']) }}">
                                    <div class="bella-search-thumb">
                                        @if($tmdb['image'])
                                            <img src="{{ $tmdb['image'] }}" class="absolute h-full w-full object-cover rounded-md" alt="{{ $tmdb['title'] }}">
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-white line-clamp-1">{{ $tmdb['title'] }}</div>
                                        <div class="text-xs text-gray-400 mt-1 line-clamp-2">{{ Str::limit($tmdb['overview'], 110) }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="bella-search-empty">{{ __('TMDB matches will appear here when local results run out.') }}</div>
                    @endif
                </div>
            </div>

            @if(count($posts) > 0 || count($tmdbResults) > 0)
                <div class="border-t border-white/10 text-center">
                    <a href="{{ route('search',$q) }}" class="inline-flex py-4 px-6 text-sm font-semibold text-gray-300 hover:text-white">
                        {{ __('View all results') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
