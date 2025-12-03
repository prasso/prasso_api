<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge"> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content=""> 
        <meta name="author" content="">
         @if (isset($site))
        <title>{{ $site->site_name ." - " }}{{ $title }}</title>
         @endif
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="/js/google-fonts-inter.css" rel="stylesheet">
         <link href="/js/google-fonts-material-icon.css" rel="stylesheet">
         <link rel="stylesheet" href="/js/google-material-design-iconic-font.2.2.0.min.css">
         <link href="/js/google-fonts-Roboto.css" rel="stylesheet"> 
        <!-- Styles -->   
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet"> 
        @if (isset($site))
        @include('partials.theme-styles', ['site' => $site])
        @endif
        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> 
        <script src="{{ asset('/js/app.js') }}" defer></script>
        @include('components.alpine-loader')
    
         @if (isset($site))
         <link rel="icon" type="image/png" href="{{$site->favicon??''}}" />
        <link rel="icon" type="image/png" href="{{ config('app.photo_url').$site->image_folder}}favicon-16x16.png" sizes="16x16">
        <link rel="icon" type="image/png" href="{{ config('app.photo_url').$site->image_folder}}favicon-32x32.png" sizes="32x32">
        <link rel="icon" sizes="192x192" href="{{ config('app.photo_url').$site->image_folder}}android-chrome-192x192.png">
        <link rel="icon" sizes="512x512" href="{{ config('app.photo_url').$site->image_folder}}android-chrome-512x512.png">
        <link rel="apple-touch-icon" href="{{ config('app.photo_url').$site->image_folder}}apple-touch-icon.png">
        
        <!-- PWA Manifest - Dynamic per site -->
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="{{ $site->main_color ?? '#000000' }}">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="{{ $site->site_name ?? config('app.name') }}">
    
        @if (isset($site->app_specific_css) && str_starts_with($site->app_specific_css, 'http') )
            <link rel="stylesheet" href="{{$site->app_specific_css}}">
        @else
            <style>{{$site->app_specific_css}}</style>
        @endif
        @if (isset($site->app_specific_js) && str_starts_with($site->app_specific_js, 'http') )
            <script src="{{$site->app_specific_js}}"></script>
        @else
            <script>{{$site->app_specific_js}}</script>
        @endif

@endif
@include('components.livewire-component-load')
    
    @livewireStyles
    </head>
    <body>
        @if (session()->has('message'))
        <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md my-3 mb-0" role="alert">
            <div>
                <p class="m-auto text-center text-sm">{{ session('message') }}</p>
            </div>
        </div>
        @endif
        <div class="font-sans text-gray-900 antialiased">
            {{ $slot }}
        </div>
        <x-footer></x-footer>
        
        <!-- PWA Hidden Login Access & Service Worker Registration -->
        <script src="{{ asset('/js/pwa-login.js') }}" defer></script>
        
@livewireScripts
    </body>
</html>
