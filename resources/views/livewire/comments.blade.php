<div class="bella-comments-shell">
    @if($model->comment != 'active' AND config('settings.comment') != 'active')
        <div class="bella-comments-panel">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="bella-section-heading !mb-1">{{ __('Comments') }}</h3>
                    <p class="text-sm text-gray-400">{{ __('Join the conversation around this title.') }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        class="bella-filter-button {{ $orderable === 'id' ? '!bg-[#E50914] !text-white !border-[#E50914]' : '' }}"
                        wire:click="orderablex('id')"
                    >
                        {{ __('Newest') }}
                    </button>
                    <button
                        class="bella-filter-button {{ $orderable === 'likes_count' ? '!bg-[#E50914] !text-white !border-[#E50914]' : '' }}"
                        wire:click="orderablex('likes_count')"
                    >
                        {{ __('Most liked') }}
                    </button>
                    <span class="bella-meta-pill">{{ __(':total comments', ['total' => $comments->count()]) }}</span>
                </div>
            </div>

            @auth
                @include('livewire.partials.comment-form',[
                    'method'=>'postComment',
                    'state'=>'newCommentState',
                    'inputId'=> 'comment',
                    'inputLabel'=> 'Your comment',
                    'button'=>'Submit comment'
                ])
            @else
                <x-form.secondary href="{{route('login')}}"
                                  class="!rounded-full !px-6 !py-3 !text-sm !bg-white/10 !border-white/10 !text-white hover:!bg-white/20">
                    {{ __('Log in to comment') }}
                </x-form.secondary>
            @endauth

            @if($comments->count())
                <div class="mt-6">
                    @foreach($comments as $comment)
                        <livewire:comment :comment="$comment" :key="$comment->id"/>
                    @endforeach
                </div>

                <div class="bella-pagination">
                    {{ $comments->links() }}
                </div>
            @else
                <p class="bella-empty-state">{{ __('No comments yet!') }}</p>
            @endif
        </div>
    @endif
</div>
