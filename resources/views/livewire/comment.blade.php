<div class="{{ $hasReplies ? '' : 'last:pb-0' }}">
    @if($isEditing)
        @include('livewire.partials.comment-form',[
            'method'=>'editComment',
            'state'=>'editState',
            'inputId'=> 'reply-comment',
            'inputLabel'=> 'Your Reply',
            'button'=>'Edit Comment'
        ])
    @else
        <article class="bella-comment-card text-base">
            <div class="flex gap-4">
                {!! gravatar($comment->user->name,$comment->user->avatarurl,'h-11 w-11 rounded-full bg-primary-500 text-xs font-bold flex items-center justify-center text-white shrink-0') !!}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-gray-400">
                        <a href="" class="hover:underline text-white font-semibold">{{ $comment->user->username }}</a>
                        @if($comment->user->account_type == 'admin')
                            <span class="bella-meta-pill !py-1 !px-2 !text-[11px]">{{ __('Moderator') }}</span>
                        @endif
                        <time class="text-xs" pubdate datetime="{{ $comment->presenter()->relativeCreatedAt() }}" title="{{ $comment->presenter()->relativeCreatedAt() }}">
                            {{ $comment->presenter()->relativeCreatedAt() }}
                        </time>
                    </div>

                    <div class="bella-comment-body mt-2">
                        {!! $comment->presenter()->replaceUserMentions($comment->presenter()->markdownBody()) !!}
                    </div>

                    <div class="bella-comment-toolbar">
                        <livewire:like :comment="$comment" :key="$comment->id"/>

                        @auth
                            @if($comment->isParent())
                                <button type="button" wire:click="$toggle('isReplying')">
                                    {{ __('Reply') }}
                                </button>
                            @endif
                        @endauth

                        @can('update',$comment)
                            <button wire:click="$toggle('isEditing')" type="button">
                                {{ __('Edit') }}
                            </button>
                        @endcan

                        @can('destroy',$comment)
                            <button
                                x-on:click="confirmCommentDeletion"
                                x-data="{
                                    confirmCommentDeletion(){
                                        if(window.confirm('You sure to delete this comment?')){
                                            @this.call('deleteComment')
                                        }
                                    }
                                }"
                                class="text-red-400"
                            >
                                {{ __('Delete') }}
                            </button>
                        @endcan
                    </div>

                    @if($isReplying)
                        <div class="mt-4">
                            @include('livewire.partials.comment-form',[
                                'method'=>'postReply',
                                'state'=>'replyState',
                                'inputId'=> 'reply-comment',
                                'inputLabel'=> 'Your Reply',
                                'button'=>'Post Reply'
                            ])
                        </div>
                    @endif

                    @if($comment->children->count())
                        <button type="button" wire:click="$toggle('hasReplies')" class="mt-3 text-sm text-gray-400 hover:text-white">
                            @if(!$hasReplies)
                                {{ __('Show :total replies',['total' => $comment->children->count()]) }}
                            @else
                                {{ __('Hide Replies') }}
                            @endif
                        </button>
                    @endif
                </div>
            </div>
        </article>
    @endif

    @if($hasReplies)
        <article class="lg:ml-14">
            @foreach($comment->children as $child)
                <livewire:comment :comment="$child" :key="$child->id"/>
            @endforeach
        </article>
    @endif
</div>
