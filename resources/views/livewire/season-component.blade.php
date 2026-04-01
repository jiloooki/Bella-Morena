<div>
    @if($selectSeason && $type == 'tv')
        <div
            class="bella-detail-panel bella-tv-episodes-panel"
            x-data="{
                openSort: @entangle('openSort').live,
                scrollOrigin: window.scrollY,
                toggleSort() {
                    if (!this.openSort) {
                        this.scrollOrigin = window.scrollY;
                    }

                    this.openSort = !this.openSort;
                },
                closeSort() {
                    this.openSort = false;
                },
                handleScroll() {
                    if (this.openSort && Math.abs(window.scrollY - this.scrollOrigin) > 50) {
                        this.closeSort();
                    }
                }
            }"
            @keydown.escape.window="closeSort()"
            @scroll.window.passive="handleScroll()"
        >
            <template x-teleport="body">
                <div>
                    <div
                        class="bella-tv-season-backdrop"
                        x-show="openSort"
                        x-cloak
                        @click="closeSort()"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                    ></div>

                    <div
                        class="bella-tv-season-sheet-shell"
                        x-show="openSort"
                        x-cloak
                        role="dialog"
                        aria-modal="true"
                        aria-label="{{ __('Select season') }}"
                        @click="closeSort()"
                        x-transition:enter="transition ease-out duration-250"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-180"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                    >
                        <div class="bella-tv-season-sheet" @click.stop>
                            <div class="bella-tv-season-sheet-header">
                                <div>
                                    <div class="bella-tv-season-sheet-label">{{ __('Seasons') }}</div>
                                    <div class="bella-tv-season-sheet-current">{{ $selectSeason->name }}</div>
                                </div>

                                <button type="button" class="bella-icon-button bella-tv-season-sheet-close" aria-label="{{ __('Close season selector') }}" @click="closeSort()">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="bella-tv-season-sheet-options {{ $seasons->count() > 5 ? 'is-grid' : '' }}">
                                @foreach($seasons as $season)
                                    <button class="bella-profile-link {{ $selectSeason->id === $season->id ? '!bg-white/10 !text-white' : '' }}"
                                            wire:click="updateSeason('{{ $season->id }}')"
                                            @click="closeSort()">
                                        {{ $season->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <div class="bella-tv-episodes-header">
                <div>
                    <h3 class="bella-section-heading !mb-1">{{ __('Episodes') }}</h3>
                    <p class="text-sm text-gray-400">{{ __('Browse available seasons and episodes.') }}</p>
                </div>
            </div>

            <div class="bella-tv-episode-toolbar">
                @if($latestEpisode)
                    <a
                        href="{{ route('episode', ['slug' => $model->slug, 'season' => $latestEpisode->season_number, 'episode' => $latestEpisode->episode_number]) }}"
                        class="bella-button bella-tv-latest-button"
                    >
                        <span>▶</span>
                        <span>{{ __('Watch Latest Episode') }}: S{{ $latestEpisode->season_number }} E{{ $latestEpisode->episode_number }}</span>
                    </a>
                @endif

                <div class="bella-tv-season-control" :class="{ 'is-open': openSort }">
                    <div class="bella-tv-season-picker">
                        <button
                            class="bella-filter-button bella-tv-season-trigger"
                            @click.prevent="toggleSort()"
                            :aria-expanded="openSort ? 'true' : 'false'"
                            aria-haspopup="dialog"
                        >
                            <span>{{ $selectSeason->name }}</span>
                            <x-ui.icon name="sort-2" class="w-4 h-4" stroke="currentColor"/>
                        </button>
                    </div>
                </div>
            </div>

            <div class="bella-tv-episode-scroll">
                @foreach($selectSeason->airedEpisodes as $episode)
                    <a
                        href="{{ route('episode',['slug'=>$episode->post->slug,'season'=>$episode->season_number,'episode'=>$episode->episode_number]) }}"
                        class="bella-tv-episode-item @if(isset(Auth::user()->id) AND $episode->isLog(Auth::user())) opacity-50 @endif"
                    >
                        <div class="bella-tv-episode-thumb">
                            @if($episode->imageurl)
                                <img src="{{ $episode->imageurl }}" alt="{{ $episode->name }}" class="w-full h-full object-cover">
                            @endif
                        </div>

                        <div class="bella-tv-episode-copy">
                            <div class="bella-tv-episode-number">
                                {{ __('Episode :number', ['number' => $episode->episode_number]) }}
                            </div>
                            <div class="bella-tv-episode-title">{{ $episode->name }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @elseif($selectSeason)
        <div class="bella-detail-panel" x-data="{ openSort: @entangle('openSort').live }">
            <div class="flex items-center justify-between gap-4 mb-5">
                <h3 class="bella-section-heading !mb-0">{{ $selectSeason->name }}</h3>

                <div class="relative">
                    <button class="bella-filter-button" @click.prevent="openSort = !openSort" :aria-expanded="openSort">
                        <x-ui.icon name="sort-2" class="w-4 h-4" stroke="currentColor"/>
                        <span>{{ __('Seasons') }}</span>
                    </button>

                    <div
                        class="bella-filter-popover !w-56"
                        @click.outside="openSort = false"
                        @keydown.escape.window="openSort = false"
                        x-show="openSort"
                        x-transition:enter="transition ease-out duration-200 transform"
                        x-transition:enter-start="opacity-0 -trangray-y-2"
                        x-transition:enter-end="opacity-100 trangray-y-0"
                        x-transition:leave="transition ease-out duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        style="display: none;"
                    >
                        <div class="flex flex-col gap-1">
                            @foreach($seasons as $season)
                                <button class="bella-profile-link" wire:click="updateSeason('{{ $season->id }}')">
                                    {{ $season->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="bella-episode-list">
                @foreach($selectSeason->airedEpisodes as $episode)
                    <a
                        href="{{ route('episode',['slug'=>$episode->post->slug,'season'=>$episode->season_number,'episode'=>$episode->episode_number]) }}"
                        class="bella-episode-link {{ $selectEpisode == $episode->episode_number ? 'is-active' : '' }} @if(isset(Auth::user()->id) AND $episode->isLog(Auth::user())) opacity-50 @endif"
                    >
                        <span class="font-semibold">{{ __('Ep #:number',['number'=>$episode->episode_number]) }}</span>
                        <span class="line-clamp-1">{{ $episode->name }}</span>
                    </a>
                @endforeach
            </div>

            <div class="flex items-center gap-4 mt-6 pt-5 border-t border-white/10">
                <div class="flex-1 text-sm text-gray-400">{{ __('Go to episode') }}</div>
                <form method="post" class="bella-filter-button !px-3" wire:submit="goto">
                    @csrf
                    <input
                        type="number"
                        required
                        wire:model.live="episode_number"
                        class="bg-transparent w-10 text-center text-sm border-0 focus:ring-0"
                        value=""
                    >
                    <button type="submit" class="text-gray-300">
                        <svg aria-hidden="true" class="w-4 h-4 rotate-90" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
