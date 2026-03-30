<div x-data="{ reportModal: @entangle('reportModal').live }">
    @if(auth()->user())
        <button class="bella-inline-action {{ $buttonClass }}" @click.prevent="reportModal = true;">
            <x-ui.icon name="flag" stroke="currentColor" class="w-5 h-5"/>
            @if($showDesktopLabel)
                <span class="bella-detail-subaction-label hidden md:inline">{{ __('Report') }}</span>
            @endif
        </button>

        <div
            class="fixed inset-0 z-50 flex items-center justify-center px-4 bella-modal-overlay"
            x-show="reportModal"
            x-cloak
        >
            <div
                class="bella-modal-card max-w-xl w-full p-7 lg:p-8"
                @click.outside="reportModal = false"
                @keydown.escape.window="reportModal = false"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-2xl font-semibold text-white">{{ __('Report') }}</h3>
                        <p class="text-sm text-gray-400 mt-1">{{ $model->title }}</p>
                    </div>
                    <button type="button" class="bella-icon-button" @click="reportModal = false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                @if(count($errors) > 0)
                    <p class="mb-5 text-xs text-red-400">
                        {{ implode(' ', $errors->all()) }}
                    </p>
                @endif

                <form method="post" wire:submit="reportForm" class="space-y-5">
                    @csrf

                    <div>
                        <x-form.label for="type" :value="__('Report')" class="!text-gray-300"/>
                        <div class="bella-filter-options mt-3">
                            @foreach(config('attr.reports') as $key => $report)
                                <div>
                                    <input type="radio" id="type{{ $key }}" name="type" value="{{ $key }}" class="hidden peer" wire:model="type"/>
                                    <label for="type{{ $key }}" class="bella-filter-label cursor-pointer">
                                        {{ __($report) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bella-form-surface p-3">
                        <x-form.label for="description" :value="__('Description')" class="!text-gray-300 px-2 pt-1"/>
                        <x-form.textarea
                            name="description"
                            placeholder="{{ __('Description') }}"
                            wire:model="description"
                            required
                            class="!bg-transparent !border-0 !shadow-none !text-white !px-2 !pb-2"
                        />
                    </div>

                    <x-form.primary wire:loading.attr="disabled" type="submit" class="w-full !rounded-full !bg-[#E50914] !border-[#E50914]">
                        {{ __('Submit') }}
                    </x-form.primary>
                </form>
            </div>
        </div>
    @else
        <a href="{{ route('login') }}" class="bella-inline-action {{ $buttonClass }}" aria-label="{{ __('Report') }}">
            <x-ui.icon name="report" fill="currentColor" class="w-5 h-5"/>
            @if($showDesktopLabel)
                <span class="bella-detail-subaction-label hidden md:inline">{{ __('Report') }}</span>
            @endif
        </a>
    @endif
</div>
