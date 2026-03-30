@if(isset($listing->tags))
    @foreach($listing->tags as $tag)
        <a href="{{ route('tag', $tag->slug) }}">{{ $tag->tag }}</a>
    @endforeach
@endif
