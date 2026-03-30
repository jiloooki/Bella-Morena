@extends('layouts.admin')
@section('content')
    @php
        $viewSort = in_array($request->input('sorting', $request->input('sort', 'desc')), ['asc', 'desc']) ? $request->input('sorting', $request->input('sort', 'desc')) : 'desc';
        $nextViewSort = $viewSort === 'desc' ? 'asc' : 'desc';
        $viewSortQuery = array_merge($request->except('page'), ['sort' => $nextViewSort, 'sorting' => $nextViewSort]);
    @endphp

    <div class="container-fluid">
        <div
            class="border border-gray-200 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-500 shadow-sm rounded-xl">
            @include('admin.partials.table-header')
            <div class="overflow-auto lg:overflow-visible">
                <table class="table-list">
                    <thead class="">
                    <tr>
                        <th scope="col">
                            <div
                                class="text-xs font-medium tracking-wide text-gray-700 dark:text-gray-200">
                                {{__('Heading')}}
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left">
                            <a
                                href="{{ route('admin.'.$config['route'].'.index', $viewSortQuery) }}"
                                class="inline-flex items-center gap-2 text-xs font-medium tracking-wide text-gray-700 transition hover:text-gray-900 dark:text-gray-200 dark:hover:text-white"
                                title="{{ $viewSort === 'desc' ? __('Currently sorted by most views first') : __('Currently sorted by least views first') }}"
                            >
                                <span>{{ __('Views') }}</span>
                                <span class="inline-flex items-center gap-1 rounded-full border border-gray-200 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-gray-500 dark:border-gray-700 dark:text-gray-300">
                                    <span>{{ $viewSort === 'desc' ? __('Top') : __('Low') }}</span>
                                    <span aria-hidden="true">{{ $viewSort === 'desc' ? '↓' : '↑' }}</span>
                                </span>
                            </a>
                        </th>
                        <th scope="col"></th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($listings as $listing)
                        <tr>
                            <td>

                                <a class="text-sm text-gray-600 dark:text-gray-200 flex items-center space-x-6 group"
                                   href="{{route('admin.'.$config['nav'].'.edit',$listing->id)}}">
                                    <div
                                        class="aspect-[2/3] rounded-md w-14 overflow-hidden relative">
                                        <img src="{{$listing->imageurl}}"
                                             class="absolute inset-0 object-cover">
                                    </div>
                                    <div class="">
                                        <div
                                            class="font-medium group-hover:underline mb-2">{{$listing->title}}</div>
                                        <div
                                            class="text-xs text-gray-400 dark:text-gray-500">{{Str::limit($listing->overview,80)}}</div>
                                    </div>
                                </a>
                            </td>
                            <td>
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    {{number_format((int) $listing->view)}}
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center justify-end text-end space-x-5">
                                    @if($listing->type == 'tv')
                                        <x-form.button-episode
                                            route="{{route('admin.episode.index', ['post_id' => $listing->id]) }}" routeEpisode="{{route('admin.episode.create', ['post_id' => $listing->id]) }}"/>
                                    @endif
                                    <x-form.button-show
                                        route="{{route($listing->type, $listing->slug) }}"/>
                                    <x-form.button-edit
                                        route="{{route('admin.'.$config['nav'].'.edit', $listing->id) }}"/>
                                    <x-form.button-delete
                                        route="{{route('admin.'.$config['nav'].'.destroy', $listing->id) }}"/>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @include('admin.partials.table-footer')
            </div>
        </div>
    </div>
@endsection
