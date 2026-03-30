<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark"
      dir="{{app()->getLocale() == 'ar' ? 'rtl' : 'ltr'}}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@if(isset($config['title']))
            {{$config['title']}}
        @else
            {{config('settings.title')}}
        @endif</title>

    <meta itemprop="name"
          content="@if(isset($config['title'])){{$config['title']}}@else{{config('settings.title')}}@endif">
    <meta itemprop="description"
          content="@if(isset($config['description'])){{$config['description']}}@else{{config('settings.description')}}@endif">
    @if(!empty($config['image']))
        <meta itemprop="image" content="{{ $config['image'] }}">
    @endif

    <meta property="og:title"
          content="@if(isset($config['title'])){{$config['title']}}@else{{config('settings.title')}}@endif">
    <meta property="og:description"
          content="@if(isset($config['description'])){{$config['description']}}@else{{config('settings.description')}}@endif">
    @if(!empty($config['image']))
        <meta property="og:image" content="{{ $config['image'] }}"/>
    @endif
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{app()->getLocale().'_'.strtoupper(app()->getLocale())}}"/>
    <meta property="og:type" content="article"/>
    <meta name="twitter:card" content="summary_large_image">

    <link rel="canonical" href="{{ url()->current() }}"/>

    @include('partials.head')
    @livewireStyles
    @livewireScriptConfig
</head>
<body class="bella-body min-h-screen flex flex-col relative" x-cloak="" x-data="{ searchOpen: false,loading:false,sidebarToggle: false,compactToggle: localStorage.getItem('compactToggle') === 'true', cookiePolicy: localStorage.getItem('cookiePolicy'), promote: localStorage.getItem('promote')}"
    x-init="$watch('cookiePolicy', val => {
  localStorage.setItem('cookiePolicy', val);
}) ; $watch('promote', val => {
  localStorage.setItem('promote', val);
}); $watch('compactToggle', val => {
  localStorage.setItem('compactToggle', val);
})">
    @include('partials.navbar',['search' => true])

    <main class="bella-main flex-1">
        @yield('content')
    </main>

    @include('partials.footer')

    <livewire:search-component/>
    <script src="{{asset('static/js/lazysizes.js')}}"></script>
    <livewire:notify-component/>
    @stack('javascript')
    <x-ui.toast/>
</body>
</html>
