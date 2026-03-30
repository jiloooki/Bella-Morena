@php
    $currentYear = date('Y');
@endphp

<div class="bella-browse-header">
    <div>
        <h1 class="bella-browse-title">{{ $param['heading'] }}</h1>
        <p class="bella-browse-subtitle">{{ __('Browse Bella Morena with live filters, instant sorting, and TMDB-assisted discovery.') }}</p>
    </div>

    <div class="bella-browse-controls">
        <button class="bella-filter-button" wire:click="filterOpen = true;">
            <x-ui.icon name="sort-2" class="w-5 h-5" stroke="currentColor" stroke-width="1.75"/>
            <span>{{ __('Filter') }}</span>
        </button>

        <div class="relative" x-data="{ openSort: @entangle('openSort') }">
            <button class="bella-filter-button" @click.prevent="openSort = !openSort" :aria-expanded="openSort">
                <span>{{ isset($sort) ? __(config('attr.sortable')[$sort]['title']) : __('Newest') }}</span>
                <x-ui.icon name="swap" class="w-4 h-4" stroke="currentColor" stroke-width="1.75"/>
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
                    @foreach(config('attr.sortable') as $key => $value)
                        <button class="bella-profile-link {{ isset($sort) && $sort === $key ? '!bg-white/10 !text-white' : '' }}" wire:click="updateSort('{{ $key }}')">
                            {{ __($value['title']) }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="fixed inset-0 z-50 bella-modal-overlay"
     x-show="filterOpen"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-out duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     aria-hidden="true"
     style="display: none;"></div>

<div id="filter-modal"
     class="fixed inset-0 z-50 overflow-hidden flex items-start top-0 lg:top-16 justify-center lg:px-6"
     role="dialog"
     aria-modal="true"
     x-show="filterOpen"
     x-transition:enter="transition ease-in-out duration-200"
     x-transition:enter-start="opacity-0 trangray-y-4"
     x-transition:enter-end="opacity-100 trangray-y-0"
     x-transition:leave="transition ease-in-out duration-200"
     x-transition:leave-start="opacity-100 trangray-y-0"
     x-transition:leave-end="opacity-0 trangray-y-4"
     style="display: none;">
    <div class="bella-modal-card h-full lg:h-auto overflow-auto lg:max-w-5xl w-full p-6 xl:p-8 max-h-full lg:rounded-2xl"
         @click.outside="filterOpen = false"
         @keydown.escape.window="filterOpen = false">
        <form wire:submit="filter" method="post">
            @csrf

            <div class="bella-filter-grid">
                <div>
                    <div class="bella-filter-group-title">{{ __('Type') }}</div>
                    <div class="bella-filter-options">
                        <div>
                            <input type="radio" id="typeall" name="type" value="all" class="hidden peer" @if(empty($type)) checked @endif>
                            <label for="typeall" class="bella-filter-label">{{ __('All') }}</label>
                        </div>
                        @foreach(config('attr.types') as $valueType => $keyType)
                            <div>
                                <input type="radio" id="type{{ $keyType }}" name="type" wire:model="type" value="{{ $valueType }}" class="hidden peer">
                                <label for="type{{ $keyType }}" class="bella-filter-label">{{ __($keyType) }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <div class="bella-filter-group-title">{{ __('Quality') }}</div>
                    <div class="bella-filter-options">
                        <div>
                            <input type="radio" id="qualityall" name="quality" value="all" class="hidden peer" @if(empty($quality)) checked @endif>
                            <label for="qualityall" class="bella-filter-label">{{ __('All') }}</label>
                        </div>
                        @foreach(config('attr.quality') as $quality)
                            <div>
                                <input type="radio" id="quality{{ $quality }}" name="quality" wire:model="quality" value="{{ $quality }}" class="hidden peer">
                                <label for="quality{{ $quality }}" class="bella-filter-label">{{ $quality }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <div class="bella-filter-group-title">{{ __('Genre') }}</div>
                <div class="bella-filter-options">
                    @foreach(\App\Models\Genre::get() as $genre)
                        <div>
                            <input type="checkbox" id="category{{ $genre->id }}" name="genre[]" wire:model="genre" value="{{ $genre->id }}" class="hidden peer">
                            <label for="category{{ $genre->id }}" class="bella-filter-label">{{ $genre->title }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 bella-filter-grid">
                <div>
                    <div class="bella-filter-group-title">{{ __('Released') }}</div>
                    <div class="bella-filter-options">
                        <div>
                            <input type="radio" id="releaseall" name="release" value="all" class="hidden peer" @if(empty($release)) checked @endif>
                            <label for="releaseall" class="bella-filter-label">{{ __('All') }}</label>
                        </div>
                        @for($i = $currentYear; $i >= ($currentYear - 5); $i--)
                            <div>
                                <input type="radio" id="release{{ $i }}" name="release" wire:model="release" value="{{ $i }}" class="hidden peer">
                                <label for="release{{ $i }}" class="bella-filter-label">{{ $i }}</label>
                            </div>
                        @endfor
                        <div>
                            <input type="radio" id="releaseolder" name="release" wire:model="release" value="{{ $currentYear-6 }}" class="hidden peer">
                            <label for="releaseolder" class="bella-filter-label">{{ __('Older') }}</label>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="bella-filter-group-title">{{ __('Country') }}</div>
                    <div class="bella-filter-options">
                        @foreach(\App\Models\Country::where('filter','active')->get() as $country)
                            <div>
                                <input type="checkbox" id="country{{ $country->id }}" name="country[]" wire:model="country" value="{{ $country->id }}" class="hidden peer">
                                <label for="country{{ $country->id }}" class="bella-filter-label">{{ $country->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <div class="bella-filter-group-title">{{ __('IMDb') }}</div>
                <div class="bella-filter-options">
                    @for ($vote = 4; $vote <= 10; $vote++)
                        <div>
                            <input type="radio" id="vote{{ $vote }}" name="vote_average" value="{{ $vote }}" class="hidden peer" wire:model="vote_average">
                            <label for="vote{{ $vote }}" class="bella-filter-label">{{ $vote }}</label>
                        </div>
                    @endfor
                </div>
            </div>

            <div class="mt-8 text-center">
                <x-form.primary type="submit" size="md" class="min-w-[16rem] !rounded-full !bg-[#E50914] !border-[#E50914]">
                    {{ __('Apply') }}
                </x-form.primary>
            </div>
        </form>
    </div>
</div>
