@extends('layouts.embed')
@section('content')
    <div class="w-full aspect-video">
        @if($listing->type == 'embed')
            <iframe class="w-full h-full"
                src="{{$listing->link}}"
                allowfullscreen
                allowtransparency
                allow="autoplay"
            ></iframe>
        @elseif($listing->type == 'mp4')
            <video class="w-full h-full" controls preload="auto"
                   poster="{{$listing->postable->post->coverurl ?? $listing->postable->coverurl}}">
                <source src="{{$listing->link}}" type="video/mp4">
                @if(method_exists($listing->postable, 'subtitles'))
                    @foreach($listing->postable->subtitles as $subtitle)
                        <track kind="captions" label="{{$subtitle->country->name}}" srclang="{{$subtitle->country->code}}" src="{{$subtitle->linkurl}}" />
                    @endforeach
                @endif
            </video>
        @elseif($listing->type == 'hls')
            <video id="hls-player" class="w-full h-full" controls preload="auto"
                   poster="{{$listing->postable->post->coverurl ?? $listing->postable->coverurl}}">
                <source src="{{$listing->link}}" type="application/x-mpegURL">
                @if(method_exists($listing->postable, 'subtitles'))
                    @foreach($listing->postable->subtitles as $subtitle)
                        <track kind="captions" label="{{$subtitle->country->name}}" srclang="{{$subtitle->country->code}}" src="{{$subtitle->linkurl}}" />
                    @endforeach
                @endif
            </video>
            @push('javascript')
                <script src="{{asset('static/js/player/plyr/plyr.hls.js')}}"></script>
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
            <iframe class="w-full h-full"
                src="{{$listing->link}}"
                allowfullscreen
                allow="autoplay"
            ></iframe>
        @endif
    </div>
@endsection
