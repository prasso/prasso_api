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
        <link rel="shortcut icon" type="image/x-icon" href="{{ $site->favicon }}">
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
         <script src="/js/jqueryui.1.12.1.min.css"></script>
         <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">
         <!-- Scripts -->
         <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.2.1/dist/alpine.js" defer></script>
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


    </head>
    <body>
        <div class="font-sans text-gray-900 antialiased">
        {!! $sitePage->description !!}
        </div>
        <x-footer></x-footer>

    </body>
</html>
