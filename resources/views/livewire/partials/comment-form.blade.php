<form class="mb-6" wire:submit="{{ $method }}">
    @csrf
    <label for="{{ $inputId }}" class="sr-only">Your message</label>

    <div class="bella-comment-form">
        <div class="flex gap-4 items-end">
            <textarea
                id="{{ $inputId }}"
                rows="3"
                class="@error($state.'.body') border-red-500 @enderror"
                placeholder="{{ __('Write a comment') }}..."
                wire:model="{{ $state }}.body"
            ></textarea>

            <button
                type="submit"
                class="bella-comment-submit shrink-0"
                wire:loading.attr="disabled"
                aria-label="{{ __($button) }}"
            >
                <svg aria-hidden="true" class="w-5 h-5 rotate-90" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path>
                </svg>
            </button>
        </div>
    </div>

    @if(!empty($users) && $users->count() > 0)
        @include('livewire.partials.dropdowns.users')
    @endif

    @error($state.'.body')
        <p class="mt-2 text-xs text-red-400">
            {{ $message }}
        </p>
    @enderror
</form>
