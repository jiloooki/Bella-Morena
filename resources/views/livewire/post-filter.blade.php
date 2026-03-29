<div class="pb-6 lg:pb-16" x-data="{filterOpen:@entangle('filterOpen'),loading:@entangle('loading')}">
    <div class="custom-container">

        @if(config('settings.listing_filter') == 'v2')
            @include('browse.partials.filterv2')
        @else
            @include('browse.partials.filterv1')
        @endif
        @if(config('settings.listing_genre') == 'active')
            @include('browse.partials.genre')
        @endif

        {{-- TMDB API Results (shown when searching) --}}
        @if(isset($tmdbResults) && count($tmdbResults) > 0)
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-300 mb-4 flex items-center gap-2">
                    <span>{{__('More results from TMDB')}}</span>
                    <span class="text-xs bg-primary-500/20 text-primary-400 rounded-full px-2 py-0.5 font-normal">{{count($tmdbResults)}}</span>
                </h3>
                <div class="grid grid-cols-2 xl:grid-cols-6 2xl:grid-cols-8 gap-6">
                    @foreach($tmdbResults as $tmdb)
                        <div class="relative group overflow-hidden">
                            <a href="{{route($tmdb['type'] === 'movie' ? 'movie' : 'tv', 'tmdb-' . $tmdb['tmdb_id'])}}"
                               class="aspect-poster relative transition overflow-hidden cursor-pointer before:absolute before:-inset-px before:bg-gradient-to-b before:to-gray-950/[.4] before:from-gray-950 before:-m-px before:z-[1] before:opacity-0 group-hover:before:opacity-100 block bg-gray-800 rounded-md">
                                @if($tmdb['image'])
                                    <img src="{{$tmdb['image']}}" class="absolute h-full w-full object-cover rounded-md" alt="{{$tmdb['title']}}" loading="lazy">
                                @endif
                                <div class="absolute right-3 top-3 w-10 h-10 items-center justify-center text-white z-20 hidden group-hover:flex">
                                    <span class="text-xs">{{$tmdb['vote_average']}}</span>
                                    <svg x="0px" y="0px" viewBox="0 0 36 36" class="absolute -inset-0 text-amber-400 bg-amber-400/20 w-10 h-10 rounded-full">
                                        <circle fill="none" stroke="currentColor" stroke-width="3" cx="18" cy="18" r="16"
                                                stroke-dasharray="{{round($tmdb['vote_average'] / 10 * 100)}} 100"
                                                stroke-linecap="round" stroke-dashoffset="0"
                                                transform="rotate(-90 18 18)"></circle>
                                    </svg>
                                </div>
                                <div class="hidden group-hover:flex absolute left-1/2 top-1/2 -translate-x-1/2 z-20 -translate-y-1/2 h-14 w-14 items-center justify-center cursor-pointer rounded-full bg-white/50 text-white transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="hi-mini hi-play h-5 w-5 translate-x-0.5">
                                        <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                    </svg>
                                </div>
                            </a>
                            <div class="pt-4 transition">
                                <div class="text-xs text-white/50 gap-x-3 mb-0.5">
                                    @if($tmdb['release_date'])
                                        <span>{{substr($tmdb['release_date'], 0, 4)}}</span>
                                    @endif
                                </div>
                                <h3 class="text-sm tracking-tighter font-medium text-gray-300 line-clamp-1">{{$tmdb['title']}}</h3>
                                <div class="text-xs text-white/50 gap-x-3 mt-1 flex items-center">
                                    <span class="text-xxs bg-primary-500/20 text-primary-400 rounded py-0.5 px-1.5">TMDB</span>
                                    <span class="text-xxs bg-gray-800 rounded py-0.5 px-1.5 text-gray-300 !ml-auto">{{$tmdb['type'] === 'movie' ? __('Movie') : __('TV Show')}}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="border-t border-gray-800 mb-8"></div>
        @endif

        <div class="grid grid-cols-2 xl:grid-cols-6 2xl:grid-cols-8 gap-6">
            @for ($i = 1; $i <=8; $i++)
                <div class="relative group animate-pulse" wire:loading wire:target="filter">
                    <div
                        class="aspect-[2/3] relative bg-gray-900 rounded-lg">
                    </div>
                    <div
                        class="pt-4 transition">
                        <div class="text-xs text-white/50 space-x-3 mb-2 flex items-center">
                            <span class="h-3 w-20 rounded-lg bg-gray-900 block"></span>
                            <span class="h-3 w-14 rounded-lg bg-gray-900 block"></span>
                        </div>
                        <h3 class="text-sm tracking-tighter font-medium text-gray-300 line-clamp-1">
                            <div class="w-10/12 h-4 rounded-lg bg-gray-900"></div>
                        </h3>
                    </div>
                </div>
            @endfor
        </div>
        <div class="grid grid-cols-2 xl:grid-cols-6 2xl:grid-cols-8 gap-6" wire:loading.remove wire:target="filter">

            @foreach($listings as $listing)
                <x-ui.post :listing="$listing" :title="$listing->title" :image="$listing->imageurl"
                           :vote="$listing->vote_average"
                           :genres="$listing->genres"/>

                @if($loop->index == 7)
                    <div class="col-span-full">
                        @include('partials.ads',['id'=> 3])
                    </div>
                @endif
                @if($loop->index == 7 AND (empty($page) OR $page == 1) AND config('settings.top_week') == 'active')

                    <div class="col-span-full">
                        <div class="xl:py-5">
                            <div class="rounded-xl h-full p-6 xl:p-8 bg-gray-900">
                                <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-5 flex items-center space-x-3">
                                    <span class="text-2xl">🔥</span>
                                    <span>{{__('Top this week')}}</span>
                                </h3>
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    @foreach($recommends as $recommend)
                                        <div class="flex space-x-8 items-center relative">
                                            <a href="{{route($recommend->type,$recommend->slug)}}"
                                               class="flex-1 group flex gap-x-6">

                                                <div
                                                    class="aspect-[2/3] w-16 relative bg-gray-900 rounded-md transition overflow-hidden cursor-pointer group-hover:opacity-60">
                                                    <img src="{{$recommend->imageurl}}"
                                                         class="absolute h-full w-full object-cover">

                                                </div>
                                                <div class="flex-1">

                                                    <div class="text-xs text-white/50 space-x-3 mb-0.5">
                                                        @if($recommend->runtime)
                                                            <span>{{__(':time min',['time' => $recommend->runtime])}}</span>
                                                        @endif
                                                        @if($recommend->release_date)
                                                            <span>{{$recommend->release_date->translatedFormat('Y')}}</span>
                                                        @endif
                                                    </div>
                                                    <h3 class="text-sm tracking-tighter group-hover:underline font-medium text-gray-300 line-clamp-1">{{$recommend->title}}</h3>
                                                    <div
                                                        class="text-xs text-white/50 space-x-3 mt-2 flex items-center">
                                                        @foreach($recommend->genres->slice(0, 1) as $genre)
                                                            <span>{{$genre->title}}</span>
                                                        @endforeach
                                                        <span
                                                            class="text-xxs bg-gray-800 rounded py-0.5 px-1.5 text-gray-300">{{$recommend->type == 'movie' ? __('Movie') : __('TV Show')}}</span>
                                                    </div>
                                                </div>

                                                <div
                                                    class="relative flex w-11 h-11 items-center justify-center text-white z-20 mr-6">
                                                    <span class="text-xs">{{$recommend->vote_average}}</span>
                                                    <svg x="0px" y="0px" viewBox="0 0 36 36"
                                                         class="absolute -inset-0 text-amber-400 bg-amber-400/20 w-11 h-11 rounded-full">
                                                        <circle fill="none" stroke="currentColor" stroke-width="3"
                                                                cx="18" cy="18" r="16"
                                                                stroke-dasharray="{{round($recommend->vote_average / 10 * 100)}} 100"
                                                                stroke-linecap="round" stroke-dashoffset="0"
                                                                transform="rotate(-90 18 18)"></circle>
                                                    </svg>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-full hidden 2xl:block">

                    </div>
                @endif
            @endforeach
        </div>
        {{ $listings->links() }}
    </div>

</div>
