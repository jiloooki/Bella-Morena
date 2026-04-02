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
        <div
            class="bella-episode-season-panel"
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
                                    <button
                                        class="bella-profile-link {{ $selectSeason->id === $season->id ? '!bg-white/10 !text-white' : '' }}"
                                        wire:click="updateSeason('{{ $season->id }}')"
                                        @click="closeSort()"
                                    >
                                        {{ $season->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <div class="bella-episode-season-header">
                <div>
                    <p class="bella-detail-summary-kicker">{{ __('Continue Watching') }}</p>
                    <h3 class="bella-section-heading !mb-1">{{ __('Other Episodes') }}</h3>
                    <p class="text-sm text-gray-400">{{ $selectSeason->name }}</p>
                </div>

                <div class="bella-episode-season-switcher">
                    <button
                        class="bella-filter-button bella-episode-season-button"
                        @click.prevent="toggleSort()"
                        :aria-expanded="openSort ? 'true' : 'false'"
                        aria-haspopup="dialog"
                    >
                        <x-ui.icon name="sort-2" class="w-4 h-4" stroke="currentColor"/>
                        <span>{{ $selectSeason->name }}</span>
                    </button>
                </div>
            </div>

            <div class="bella-episode-list bella-episode-season-scroll">
                @foreach($selectSeason->airedEpisodes as $episode)
                    <a
                        href="{{ route('episode',['slug'=>$episode->post->slug,'season'=>$episode->season_number,'episode'=>$episode->episode_number]) }}"
                        class="bella-tv-episode-item bella-episode-sidebar-item {{ $currentEpisodeId == $episode->id ? 'is-active' : '' }} @if(isset(Auth::user()->id) AND $episode->isLog(Auth::user())) opacity-50 @endif"
                        @if($currentEpisodeId == $episode->id) aria-current="page" @endif
                    >
                        <div class="bella-tv-episode-thumb" aria-hidden="true">
                            @if($episode->imageurl)
                                <img src="{{ $episode->imageurl }}" alt="{{ $episode->name }}" class="w-full h-full object-cover">
                            @endif
                        </div>

                        <div class="bella-tv-episode-copy bella-episode-sidebar-copy">
                            <div class="bella-episode-sidebar-meta-row">
                                <div class="bella-tv-episode-number">
                                    {{ __('Episode :number', ['number' => $episode->episode_number]) }}
                                </div>

                                @if($currentEpisodeId == $episode->id)
                                    <span class="bella-episode-sidebar-badge">
                                        <span class="bella-episode-sidebar-badge-dot" aria-hidden="true"></span>
                                        <span>{{ __('Now Playing') }}</span>
                                    </span>
                                @endif
                            </div>
                            <div class="bella-tv-episode-title">{{ $episode->name }}</div>

                            @if($episode->air_date)
                                <div class="bella-episode-sidebar-date">{{ $episode->air_date->translatedFormat('d M, Y') }}</div>
                            @elseif($episode->runtime)
                                <div class="bella-episode-sidebar-date">{{ __(':time min', ['time' => $episode->runtime]) }}</div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
