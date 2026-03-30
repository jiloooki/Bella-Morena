@if(config('settings.promote_text'))
    <div class="fixed bottom-4 left-0 right-0 z-40 px-4" x-show="!promote" x-cloak>
        <div class="bella-promote-banner">
            <div class="flex items-center gap-4">
                <div class="bella-promote-icon">
                    <img src="{{asset('static/img/promote.png')}}" class="h-10 w-10 object-contain" alt="">
                </div>
                <a href="{{config('settings.promote_link')}}" class="bella-promote-copy">{{config('settings.promote_text')}}</a>
                <button class="bella-icon-button ml-auto" @click="promote = true" type="button" aria-label="Close">
                    <x-ui.icon name="close" class="w-5 h-5" fill="currentColor"/>
                </button>
            </div>
        </div>
    </div>
@endif

<footer class="bella-footer">
    <div class="bella-footer-shell">
        <div class="bella-footer-grid">
            <div class="bella-footer-brand">
                <a href="{{ route('index') }}" class="bella-brand text-3xl">Bella Morena</a>
                <p class="bella-footer-copy">
                    {{ config('settings.site_about') ?: __('Stream movies and TV shows with a cinematic experience inspired by the best in entertainment.') }}
                </p>
            </div>

            <div>
                <h4 class="bella-footer-heading">{{ __('Browse') }}</h4>
                <div class="bella-footer-links">
                    <a href="{{ route('movies') }}">{{ __('Movies') }}</a>
                    <a href="{{ route('tvshows') }}">{{ __('TV Shows') }}</a>
                    <a href="{{ route('trending') }}">{{ __('New & Popular') }}</a>
                    <a href="{{ route('topimdb') }}">{{ __('Top Rated') }}</a>
                    <a href="{{ route('collections') }}">{{ __('Collections') }}</a>
                </div>
            </div>

            <div>
                <h4 class="bella-footer-heading">{{ __('Explore') }}</h4>
                <div class="bella-footer-links">
                    <a href="{{ route('blog') }}">{{ __('Blog') }}</a>
                    <a href="{{ route('request') }}">{{ __('Request') }}</a>
                    <a href="{{ route('peoples') }}">{{ __('People') }}</a>
                    <a href="{{ route('contact') }}">{{ __('Contact') }}</a>
                    @foreach(config('pages')->take(2) as $page)
                        <a href="{{ route('page', $page->slug) }}">{{ $page->title }}</a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bella-footer-bottom">
            <div>© {{date('Y')}} Bella Morena. {{ __('All rights reserved.') }}</div>
            <div class="bella-footer-socials">
                @if(config('settings.twitter'))
                    <a href="{{config('settings.twitter')}}" target="_blank">{{ __('Twitter') }}</a>
                @endif
                @if(config('settings.facebook'))
                    <a href="{{config('settings.facebook')}}" target="_blank">{{ __('Facebook') }}</a>
                @endif
                @if(config('settings.instagram'))
                    <a href="{{config('settings.instagram')}}" target="_blank">{{ __('Instagram') }}</a>
                @endif
                @if(config('settings.youtube'))
                    <a href="{{config('settings.youtube')}}" target="_blank">{{ __('YouTube') }}</a>
                @endif
            </div>
        </div>
    </div>
</footer>

@if(config('settings.cookie'))
    <div
        class="fixed bottom-4 right-4 z-[60] sm:max-w-lg" x-show="!cookiePolicy" x-cloak>
        <div class="p-7 bg-white rounded-2xl shadow-sm dark:bg-gray-800">
            <div class="flex gap-x-6">
                <svg class="h-10" xmlns="http://www.w3.org/2000/svg"
                     xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                     viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
<path style="fill:#FFC033;" d="M471.801,434.713C423.206,484.554,358.118,512,288.536,512c-13.594,0-26.945-1.07-39.97-3.114
    C126.312,489.645,32.533,383.558,32.533,255.997c0-68.08,26.308-132.124,74.091-180.327c38.833-39.172,88.241-64.261,141.941-72.588
    c12.376-1.936,24.968-2.965,37.723-3.074c27.798-0.217,55.217,3.967,81.444,12.498c8.652,2.816,15.49,9.681,18.279,18.347
    c2.789,8.652,1.232,18.184-4.143,25.482c-19.308,26.254-29.517,57.41-29.517,90.109c0,48.636,22.422,93.197,61.54,122.267
    c8.896,6.608,12.863,18.239,9.884,28.949c-1.977,7.095-2.979,14.434-2.979,21.813c0,30.574,16.898,58.29,44.087,72.358
    c7.555,3.9,12.877,11.252,14.244,19.674C480.48,419.927,477.745,428.606,471.801,434.713z"/>
                    <path style="fill:#F9A926;" d="M471.801,434.713C423.206,484.554,358.118,512,288.536,512c-13.594,0-26.945-1.07-39.97-3.114V3.082
    c12.376-1.936,24.968-2.965,37.723-3.074c27.798-0.217,55.217,3.967,81.444,12.498c8.652,2.816,15.49,9.681,18.279,18.347
    c2.789,8.652,1.232,18.184-4.143,25.482c-19.308,26.254-29.517,57.41-29.517,90.109c0,48.636,22.422,93.197,61.54,122.267
    c8.896,6.608,12.863,18.239,9.884,28.949c-1.977,7.095-2.979,14.434-2.979,21.813c0,30.574,16.898,58.29,44.087,72.358
    c7.555,3.9,12.877,11.252,14.244,19.674C480.48,419.927,477.745,428.606,471.801,434.713z"/>
                    <path style="fill:#A6673A;" d="M270.027,177.519c0,31.237-25.401,56.638-56.638,56.638s-56.638-25.401-56.638-56.638
    s25.401-56.638,56.638-56.638S270.027,146.282,270.027,177.519z"/>
                    <path style="fill:#99522E;" d="M270.027,177.519c0,31.237-25.401,56.638-56.638,56.638V120.88
    C244.625,120.88,270.027,146.282,270.027,177.519z"/>
                    <path style="fill:#A6673A;" d="M253.63,315.709c0,35.665-29.03,64.681-64.695,64.681s-64.681-29.016-64.681-64.681
    s29.016-64.695,64.681-64.695S253.63,280.044,253.63,315.709z"/>
                    <path style="fill:#99522E;" d="M253.63,315.709c0,35.665-29.03,64.681-64.695,64.681V251.014
    C224.6,251.014,253.63,280.044,253.63,315.709z"/>
                    <path style="fill:#A6673A;" d="M356.751,362.314c0,27.134-22.084,49.218-49.232,49.218c-27.134,0-49.218-22.084-49.218-49.218
    c0-27.148,22.084-49.232,49.218-49.232C334.667,313.082,356.751,335.166,356.751,362.314z"/>
                    <path style="fill:#99522E;" d="M356.751,362.314c0,27.134-22.084,49.218-49.232,49.218v-98.45
    C334.667,313.082,356.751,335.166,356.751,362.314z"/>
</svg>

                <p class="text-sm text-gray-800 dark:text-gray-200">
                    {!! __('By browsing this website, you accept our :cookie.', ['cookie' => mb_strtolower('<a href="'.config('settings.cookie_url').'" target="_blank" class="inline-flex items-center gap-x-1.5 text-primary-500 decoration-2 hover:underline font-medium">'. __('Cookies Policy').'</a>')]) !!}
                </p>

                <div>
                    <button type="button"
                            class="inline-flex rounded-full p-2 text-gray-500 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-50 focus:ring-gray-600  dark:hover:bg-gray-600 dark:text-gray-400"
                            @click="cookiePolicy = true">
                        <span class="sr-only">Dismiss</span>
                        <x-ui.icon name="close" class="w-5 h-5" fill="currentColor"/>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
