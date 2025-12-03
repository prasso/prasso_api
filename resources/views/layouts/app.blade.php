<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(isset($site))
    <title>{{ $site->site_name }} - {{ $title??'' }}</title>
@else
    <title>{{ $title??'' }}</title>
@endif
    <link href="/js/google-fonts-inter.css" rel="stylesheet">
    <link href="/js/google-fonts-material-icon.css" rel="stylesheet">
    <link rel="stylesheet" href="/js/google-material-design-iconic-font.2.2.0.min.css">
    <link href="/js/google-fonts-Roboto.css" rel="stylesheet"> 

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" defer></script>
    @include('components.alpine-loader')
    
    <!-- Styles -->   
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet"> 
    @if(isset($site))
    @include('partials.theme-styles', ['site' => $site])
    @endif
    @include('components.livewire-config')
    @livewireStyles
    {{ $extracss ?? '' }}
    @if(isset($site))
    <link rel="icon" type="image/png" href="{{$site->favicon??''}}" />
    <link rel="icon" type="image/png" href="{{ config('app.photo_url').$site->image_folder}}favicon-16x16.png" sizes="16x16">
    <link rel="icon" type="image/png" href="{{ config('app.photo_url').$site->image_folder}}favicon-32x32.png" sizes="32x32">
    <link rel="icon" sizes="192x192" href="{{ config('app.photo_url').$site->image_folder}}android-chrome-192x192.png">
    <link rel="icon" sizes="512x512" href="{{ config('app.photo_url').$site->image_folder}}android-chrome-512x512.png">
    <link rel="apple-touch-icon" href="{{ config('app.photo_url').$site->image_folder}}apple-touch-icon.png">
    @endif
    
    <!-- PWA Manifest - Dynamic per site -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="{{ $site->main_color ?? '#000000' }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $site->site_name ?? config('app.name') }}">
    
   @include('components.livewire-component-load')
    
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @if (isset($header))
        @if(isset($site))
        @livewire('navigation-dropdown')
        @endif
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

        <!-- Extra Scripts -->
        {{ $extrajs ?? ''}}
        
        <script>
            // Ensure jQuery is loaded and DOM is ready
            document.addEventListener('DOMContentLoaded', function() {
                // Any jQuery initialization code can go here
            });
        </script>

        @if (   isset($site) && isset($site->app_specific_css) && str_starts_with($site->app_specific_css, 'http') )
            <link rel="stylesheet" href="{{$site->app_specific_css}}">
        @else
        @if (   isset($site))<style>{{ $site->app_specific_css }}</style>@endif
        @endif
        @if (  isset($site) && isset($site->app_specific_js) && str_starts_with($site->app_specific_js, 'http') )
            <script src="{{$site->app_specific_js}}"></script>
        @else
        @if(isset($site))<script>{{$site->app_specific_js}}</script>@endif
        @endif

        <script src="{{ asset('/js/app.js') }}" defer></script>

        @livewireScripts

        @stack('scripts')
        
        <!-- PWA Hidden Login Access & Service Worker Registration -->
        <script src="{{ asset('/js/pwa-login.js') }}" defer></script>
</body>

</html>