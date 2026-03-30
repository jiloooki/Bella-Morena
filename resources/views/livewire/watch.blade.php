<div class="bella-player-shell" x-data="{stream:'0'}">
    @if((($listing->type == 'movie' AND $listing->member == 'active') OR (isset($listing->post->type) AND $listing->post->type == 'tv' AND $listing->post->member == 'active')) AND (auth()->check() && (empty(auth()->user()->plan_recurring_at) OR auth()->user()->plan_recurring_at < now()) OR !auth()->check()))
        <div class="bella-player-frame">
            <div class="bella-player-media relative overflow-hidden">
                <img src="{{ $cover }}" class="absolute inset-0 h-full w-full object-cover" alt="">
                <div class="absolute inset-0 bg-black/70"></div>
                <div class="relative z-10 flex h-full w-full items-center justify-center px-6 text-center">
                    <div class="max-w-3xl">
                        <h3 class="text-3xl lg:text-5xl font-bold text-white mb-4 tracking-tight">{{ __('Exclusive to subscriber') }}</h3>
                        <p class="text-gray-300 text-base lg:text-lg">{{ __('You have to subscribe to watch') }}</p>
                        <x-form.primary size="lg" href="{{ route('subscription.index') }}"
                                        class="!px-8 xl:!px-14 text-sm mt-6 !rounded-full !bg-[#E50914] !border-[#E50914]">
                            {{ __('Subscribe') }}
                        </x-form.primary>
                    </div>
                </div>
            </div>
        </div>
    @else
        @if(config('settings.preloader') == 'active' AND $isPreloader == true)
            <div class="bella-player-frame">
                <div class="bella-player-media relative overflow-hidden">
                    <img src="{{ $cover }}" class="absolute inset-0 h-full w-full object-cover" alt="">
                    <div class="absolute inset-0 bg-black/65"></div>
                    <div class="relative z-10 flex h-full w-full items-center justify-center">
                        <button
                            class="bella-card-play !w-20 !h-20 text-lg hover:scale-110"
                            wire:click="watching"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-8 h-8 translate-x-1">
                                <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @else
            @if(empty($videos))
                <div class="bella-player-frame">
                    <div class="bella-player-media relative overflow-hidden">
                        <img src="{{ $cover }}" class="absolute inset-0 h-full w-full object-cover" alt="">
                        <div class="absolute inset-0 bg-black/70"></div>
                        <div class="relative z-10 flex h-full w-full items-center justify-center px-6 text-center">
                            <div class="max-w-2xl">
                                <h3 class="text-3xl lg:text-5xl font-bold text-white mb-4 tracking-tight">{{ __('Upcoming') }}</h3>
                                <p class="text-gray-300 text-base lg:text-lg">{{ __('Stay tuned, video will be added soon') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bella-player-frame">
                    <div class="bella-player-media">
                        <iframe id="video-iframe" width="1280" height="720"
                                src="{{ $videos[0]['link'] ?? '' }}" title="Video player" frameborder="0"
                                allowfullscreen
                                allowtransparency></iframe>
                    </div>

                    @if(count($videos) > 1)
                        <div class="bella-player-toolbar">
                            <div class="text-sm text-gray-400">{{ __('Choose a stream source') }}</div>
                            <ul class="flex flex-wrap gap-3">
                                @foreach($videos as $key => $video)
                                    <li>
                                        <button
                                            class="bella-filter-button"
                                            :class="{ '!bg-[#E50914] !text-white !border-[#E50914]': stream === '{{ $loop->index }}' }"
                                            x-on:click="stream = '{{ $loop->index }}'; document.getElementById('video-iframe').src = '{{ $video['link'] }}';"
                                        >
                                            <x-ui.icon name="link" class="w-4 h-4" fill="currentColor"/>
                                            <span>{{ $video['label'] ?? __('Stream').' #'.$key+1 }}</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif
        @endif
    @endif
</div>
