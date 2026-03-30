<button wire:click="like"
        class="inline-flex items-center gap-2 text-xs text-gray-400 hover:text-white disabled:cursor-not-allowed {{ $comment->isLiked() ? '!text-[#E50914]' : '' }}">
    <span class="bella-inline-action !w-9 !h-9 !min-w-0 !p-0 {{ $comment->isLiked() ? '!bg-[#E50914]/20 !border-[#E50914]/30 !text-[#E50914]' : '' }}">
        <x-ui.icon name="like" fill="currentColor" class="w-4 h-4"/>
    </span>
    <span class="font-medium">{{ $count }}</span>
    <span class="sr-only">likes</span>
</button>
