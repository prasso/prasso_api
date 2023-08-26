<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $site->site_name }} - {{ $title??'' }}</title>
    <link href="/js/google-fonts-inter.css" rel="stylesheet">
    <link href="/js/google-fonts-material-icon.css" rel="stylesheet">
    <link rel="stylesheet" href="/js/google-material-design-iconic-font.2.2.0.min.css">
    <link href="/js/google-fonts-Roboto.css" rel="stylesheet"> 

    <!-- Styles -->   
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet"> 
    <!-- Scripts -->
    <script src="/js/jquery1.10.0.min.js"></script> 
    <script src="{{ asset('/js/app.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.2/dist/alpine.min.js" defer></script>
    <link rel="stylesheet" href="/js/jqueryui.1.12.1.min.css">
    
    @livewireStyles
    {{ $extracss ?? '' }}

    <link rel="icon" type="image/png" href="{{$site->favicon??''}}" />
    <link rel="icon" type="image/png" href="{{ config('app.photo_url').$site->image_folder}}favicon-16x16.png" sizes="16x16">
    <link rel="icon" type="image/png" href="{{ config('app.photo_url').$site->image_folder}}favicon-32x32.png" sizes="32x32">
    <link rel="icon" sizes="192x192" href="{{ config('app.photo_url').$site->image_folder}}android-chrome-192x192.png">
    <link rel="icon" sizes="512x512" href="{{ config('app.photo_url').$site->image_folder}}android-chrome-512x512.png">
    <link rel="apple-touch-icon" href="{{ config('app.photo_url').$site->image_folder}}apple-touch-icon.png">
    @if (isset($site->app_specific_css) && str_starts_with($site->app_specific_css, 'http') )
        <link rel="stylesheet" href="{{$site->app_specific_css}}">
    @else
        <style>{{ $site->app_specific_css }}</style>
    @endif
    @if (isset($site->app_specific_js) && str_starts_with($site->app_specific_js, 'http') )
        <script src="{{$site->app_specific_js}}"></script>
    @else
        <script>{{$site->app_specific_js}}</script>
    @endif


</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @if (isset($header))
        @livewire('navigation-dropdown')

        <!-- Page Heading -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endif
        <!-- Page Content -->
        @if (session()->has('message'))
        <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md my-3 mb-0" role="alert">
            <div>
                <p class="m-auto text-center text-sm">{{ session('message') }}</p>
            </div>
        </div>
        @endif
        <main>
            {{ $slot }}
        </main>

        <x-footer></x-footer>

        @stack('modals')

        <!-- Scripts -->
        {{ $extrajs ?? ''}}
        <script src="{{ asset('/js/app.js') }}" defer></script>
        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.2/dist/alpine.min.js" defer></script>

        @livewireScripts

</body>

</html>