<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $site->site_name }} - {{ $title??'' }}</title>

        <meta name="description" content="{{ $title??'' }}" />
        <meta property="og:title" content="{{ $site->site_name }} - {{ $title??'' }}" />
        <meta property="og:description" content="{{ $title??'' }}" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="{{ $page_short_url }}" />
        <meta property="og:image" content="{{ $site->logo_image }}" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="{{ $site->site_name }} - {{ $title??'' }}" />
        <meta name="twitter:description" content="{{ $title??'' }}" />
        <meta name="twitter:image" content="{{ $site->logo_image }}" />

        <link rel="icon" type="image/png" href="{{$site->favicon??''}}" />
        <link rel="icon" type="image/png" href="{{ config('app.photo_url').$site->image_folder}}favicon-16x16.png" sizes="16x16">
        <link rel="icon" type="image/png" href="{{ config('app.photo_url').$site->image_folder}}favicon-32x32.png" sizes="32x32">
        <link rel="icon" sizes="192x192" href="{{ config('app.photo_url').$site->image_folder}}android-chrome-192x192.png">
        <link rel="icon" sizes="512x512" href="{{ config('app.photo_url').$site->image_folder}}android-chrome-512x512.png">
        <link rel="apple-touch-icon" href="{{ config('app.photo_url').$site->image_folder}}apple-touch-icon.png">
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        @if (isset($masterPage))
        {!!  $masterPage->js !!}
        {!!  $masterPage->css !!}
        @else
        <link href="/js/google-fonts-inter.css" rel="stylesheet">
         <link href="/js/google-fonts-material-icon.css" rel="stylesheet">
         <link rel="stylesheet" href="/js/google-material-design-iconic-font.2.2.0.min.css">
         <link href="/js/google-fonts-Roboto.css" rel="stylesheet"> 
         <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">
         <!-- Scripts -->
         @include('components.alpine-loader')
         <link rel="icon" type="image/png" href="{{$site->favicon??''}}" />
        @endif

        @if (isset($site->app_specific_js) && str_starts_with($site->app_specific_js, 'http') )
            <script src="{!! $site->app_specific_js !!}"></script>
        @else
            <script>{!! $site->app_specific_js !!}</script>
        @endif

     
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        @if (isset($site->app_specific_css) && str_starts_with($site->app_specific_css, 'http') )
            <link rel="stylesheet" href="{{$site->app_specific_css}}">
        @else
            <style>{!! $site->app_specific_css !!}</style>
        @endif
        @if (isset($sitePage->style)  )
            <style>{!! $sitePage->style !!}</style>
        @endif
    <!-- Include the Livewire AJAX loader script -->
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
        {!! $sitePage->description !!}
        </div>
        <x-footer></x-footer>

    </body>

   @livewireScripts
</html>
