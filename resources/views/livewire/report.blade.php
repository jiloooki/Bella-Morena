    <div x-data="{ reportModal: @entangle('reportModal').live, reportType: @entangle('type').live }" class="bella-report-action-wrap">
    @if(auth()->user())
        <button type="button" class="bella-inline-action {{ $buttonClass }}" @click.prevent="reportModal = true;">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.1"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="w-4 h-4 shrink-0"
                aria-hidden="true"
            >
                <path d="M6.5 20.75V4.5"/>
                <path d="M6.5 5.35L15.4 4 13.8 7.1 15.4 10.2 6.5 11.55"/>
                <path d="M5 20.75H12.2"/>
            </svg>
            @if($showDesktopLabel)
                <span class="bella-detail-subaction-label bella-movie-secondary-label">{{ __('Report') }}</span>
            @endif
        </button>

        <template x-teleport="body">
            <div>
                <div
                    class="fixed inset-0 bella-modal-overlay bella-report-modal-backdrop"
                    x-show="reportModal"
                    x-cloak
                    @click="reportModal = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                ></div>

                <div
                    class="fixed inset-0 flex items-center justify-center px-4 sm:px-6 bella-report-modal-shell"
                    x-show="reportModal"
                    x-cloak
                    role="dialog"
                    aria-modal="true"
                    @keydown.escape.window="reportModal = false"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                >
                    <div
                        class="bella-modal-card max-w-xl w-full p-7 lg:p-8"
                        @click.outside="reportModal = false"
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
                                            <input type="radio" id="type{{ $key }}" name="type" value="{{ $key }}" class="hidden peer" x-model="reportType"/>
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
                                    placeholder="{{ __('Optional: Tell us more about the problem...') }}"
                                    wire:model="description"
                                    class="!bg-transparent !border-0 !shadow-none !text-white !px-2 !pb-2"
                                />
                            </div>

                            <x-form.primary
                                wire:loading.attr="disabled"
                                x-bind:disabled="!reportType"
                                x-bind:class="!reportType ? 'opacity-50 cursor-not-allowed' : ''"
                                type="submit"
                                class="w-full !rounded-full !bg-[#E50914] !border-[#E50914]"
                            >
                                {{ __('Submit') }}
                            </x-form.primary>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    @else
        <a href="{{ route('login') }}" class="bella-inline-action {{ $buttonClass }}" aria-label="{{ __('Report') }}">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.1"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="w-4 h-4 shrink-0"
                aria-hidden="true"
            >
                <path d="M6.5 20.75V4.5"/>
                <path d="M6.5 5.35L15.4 4 13.8 7.1 15.4 10.2 6.5 11.55"/>
                <path d="M5 20.75H12.2"/>
            </svg>
            @if($showDesktopLabel)
                <span class="bella-detail-subaction-label bella-movie-secondary-label">{{ __('Report') }}</span>
            @endif
        </a>
    @endif
</div>
