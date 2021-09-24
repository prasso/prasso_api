<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $site->site_name }} - {{ $title??'' }}</title>

        <!-- Fonts -->
        <script src="/js/jquery1.10.0.min.js"></script> 
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="/js/google-fonts-inter.css" rel="stylesheet">
        <link href="/js/google-fonts-material-icon.css" rel="stylesheet">
        <link rel="stylesheet" href="/js/google-material-design-iconic-font.2.2.0.min.css">
        <link href="/js/google-fonts-Roboto.css" rel="stylesheet"> 
        <script src="/js/jqueryui.1.12.1.min.css"></script>

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">
        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.2.1/dist/alpine.js" defer></script>
    </head>
    <body>
        <div class="font-sans text-gray-900 antialiased">
            {!!  $sitePage->description !!}
        </div>
        <x-footer></x-footer>

    </body>
</html>
