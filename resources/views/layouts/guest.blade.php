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
        <script src="{{ asset('/js/app.js') }}" defer></script>
        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.2/dist/alpine.min.js" defer></script>
        
         @if (isset($site))
        <link rel="shortcut icon" type="image/png" href="{{ config('app.photo_url').$site->image_folder}}favicon.ico">
      
        <link rel="icon" type="image/png" href="{{ config('app.photo_url').$site->image_folder}}favicon-16x16.png" sizes="16x16">
        <link rel="icon" type="image/png" href="{{ config('app.photo_url').$site->image_folder}}favicon-32x32.png" sizes="32x32">
        <link rel="icon" sizes="192x192" href="{{ config('app.photo_url').$site->image_folder}}android-chrome-192x192.png">
        <link rel="icon" sizes="512x512" href="{{ config('app.photo_url').$site->image_folder}}android-chrome-512x512.png">
        <link rel="apple-touch-icon" href="{{ config('app.photo_url').$site->image_folder}}apple-touch-icon.png">
    
        <style>{{$site->app_specific_css}}</style>
@endif
    </head>
    <body>
        <div class="font-sans text-gray-900 antialiased">
            {{ $slot }}
        </div>
        <x-footer></x-footer>

    </body>
</html>
