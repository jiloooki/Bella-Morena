@props([
    'listings' => collect(),
    'module' => null,
    'layout' => null,
    'card' => 'post',
    'heading' => null,
    'seeAll' => null,
    'variant' => null,
])

@php
    $items = collect($listings)->filter();
    $title = $heading ?? ($module->title ?? __('Featured'));
@endphp

@if($items->isNotEmpty())
    <section class="bella-row">
        <div class="bella-row-shell">
            <div class="bella-row-header">
                <h2 class="bella-row-title">{{ $title }}</h2>

                @if($seeAll)
                    <a href="{{ $seeAll }}" class="bella-row-link">{{ __('See all') }}</a>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-x-4 gap-y-6 sm:gap-5 md:grid-cols-4 xl:grid-cols-7">
                @foreach($items as $listing)
                    @if($card === 'episode')
                        <x-ui.episode :listing="$listing" />
                    @elseif($card === 'broadcast')
                        <x-ui.broadcast :listing="$listing" />
                    @else
                        <x-ui.post :listing="$listing" :showReleaseBadge="$variant === 'coming-soon'" />
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endif
