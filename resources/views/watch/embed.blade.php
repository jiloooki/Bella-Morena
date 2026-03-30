@extends('layouts.embed')

@section('content')
    @php
        $isEpisodeVideo = method_exists($listing->postable, 'post');
        $title = $isEpisodeVideo ? ($listing->postable->name ?: $listing->postable->post->title) : $listing->postable->title;
    @endphp

    <div class="bella-player-screen">
        <div class="bella-player-topbar">
            <div class="flex items-center gap-4 min-w-0">
                <button type="button" onclick="window.history.back()" class="bella-icon-button">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 18 9 12l6-6"/>
                    </svg>
                </button>
                <div class="min-w-0">
                    <div class="bella-brand text-xl">Bella Morena</div>
                    <div class="text-sm text-gray-300 line-clamp-1">{{ $title }}</div>
                </div>
            </div>
        </div>

        <div class="bella-player-frame">
            @if($listing->type == 'embed')
                <iframe
                    src="{{ $listing->link }}"
                    allowfullscreen
                    allowtransparency
                    allow="autoplay"
                ></iframe>
            @elseif($listing->type == 'mp4')
                <video controls preload="auto"
                       poster="{{ $listing->postable->post->coverurl ?? $listing->postable->coverurl }}">
                    <source src="{{ $listing->link }}" type="video/mp4">
                    @if(method_exists($listing->postable, 'subtitles'))
                        @foreach($listing->postable->subtitles as $subtitle)
                            <track kind="captions" label="{{ $subtitle->country->name }}" srclang="{{ $subtitle->country->code }}" src="{{ $subtitle->linkurl }}" />
                        @endforeach
                    @endif
                </video>
            @elseif($listing->type == 'hls')
                <video id="hls-player" controls preload="auto"
                       poster="{{ $listing->postable->post->coverurl ?? $listing->postable->coverurl }}">
                    <source src="{{ $listing->link }}" type="application/x-mpegURL">
                    @if(method_exists($listing->postable, 'subtitles'))
                        @foreach($listing->postable->subtitles as $subtitle)
                            <track kind="captions" label="{{ $subtitle->country->name }}" srclang="{{ $subtitle->country->code }}" src="{{ $subtitle->linkurl }}" />
                        @endforeach
                    @endif
                </video>
                @push('javascript')
                    <script src="{{ asset('static/js/player/plyr/plyr.hls.js') }}"></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var video = document.getElementById('hls-player');
                            var src = video.querySelector('source').src;
                            if (Hls.isSupported()) {
                                var hls = new Hls();
                                hls.loadSource(src);
                                hls.attachMedia(video);
                            }
                        });
                    </script>
                @endpush
            @elseif($listing->type == 'youtube')
                <iframe
                    src="{{ $listing->link }}"
                    allowfullscreen
                    allow="autoplay"
                ></iframe>
            @endif
        </div>
    </div>
@endsection
