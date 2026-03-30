@php
    $currentLabel = $isWatchlist ? $activeLabel : $label;
@endphp

<button
    class="bella-inline-action is-wide tooltip {{ $buttonClass }} {{ $isWatchlist ? '!bg-[#E50914] !text-white !border-[#E50914]' : '' }}"
    data-tippy-content="{{ $currentLabel }}"
    wire:click.debounce.200ms="watchlist"
    wire:loading.attr="disabled"
>
    <x-ui.icon name="library-add" stroke="currentColor" class="w-5 h-5"/>
    <span class="{{ $showTextAlways ? '' : 'hidden sm:inline' }}">{{ $currentLabel }}</span>
</button>
