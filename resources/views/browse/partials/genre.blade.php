<div class="pb-8">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
        @foreach($genres as $genre)
            <a href="{{ route('genre',['genre' => $genre->slug]) }}"
               class="rounded-[1.25rem] p-5 overflow-hidden relative text-white group min-h-[11rem] flex flex-col justify-between shadow-2xl"
               style="background: linear-gradient(180deg, {{ $genre->color }} 0%, rgba(20,20,20,0.88) 100%);">
                <div>
                    <div class="text-lg font-semibold">{{ $genre->title }}</div>
                    <div class="text-xs opacity-70 mt-2">{{ __(':total movie & tv show',['total' => $genre->posts_count]) }}</div>
                </div>

                <div class="absolute -right-3 -bottom-3 w-24 aspect-square rounded-[1.5rem] overflow-hidden rotate-12 group-hover:rotate-6 group-hover:scale-105 transition-all">
                    {!! picture($genre->getImage(),config('attr.poster.size_x').','.config('attr.poster.size_y'),'absolute h-full w-full object-cover rounded-[1.5rem]',$genre->title,'post') !!}
                </div>
            </a>
        @endforeach
    </div>
</div>
