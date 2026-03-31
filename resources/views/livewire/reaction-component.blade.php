<div class="flex items-center gap-2 flex-nowrap {{ $wrapperClass }}">
    <button
        id="like"
        class="bella-inline-action is-wide {{ $buttonClass }} {{ $isReaction == 'like' ? '!bg-[#15803d] !border-[#15803d] !text-white' : '' }}"
        wire:click="reactionButton('like')"
        wire:loading.attr="disabled"
        aria-label="{{ __('Like') }}"
    >
        @if($hideLikeLabel)
            <span class="bella-like-icon-wrap">
                <x-ui.icon name="like" fill="currentColor" stroke="none" class="w-4 h-4" stroke-width="2"/>
                <span class="bella-like-count">{{ (int) $model->likes()->count() }}</span>
            </span>
        @else
            <x-ui.icon name="like" fill="currentColor" stroke="none" class="w-4 h-4" stroke-width="2"/>
            <span class="hidden sm:inline">{{ __('Like') }}</span>
            <span class="bella-like-count">{{ (int) $model->likes()->count() }}</span>
        @endif
    </button>

    <button
        id="dislike"
        class="bella-inline-action {{ $buttonClass }} {{ $isReaction == 'dislike' ? '!bg-[#b91c1c] !border-[#b91c1c] !text-white' : '' }}"
        wire:click="reactionButton('dislike')"
        wire:loading.attr="disabled"
        aria-label="{{ __('Dislike') }}"
    >
        <x-ui.icon name="dislike" fill="currentColor" stroke="none" class="w-4 h-4"/>
    </button>
</div>
