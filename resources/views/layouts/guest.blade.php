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
        @if (isset($masterPage))
            {!!  $masterPage->css !!}
            {!!  $masterPage->js !!}
         @endif
        
         @if (isset($site))
         <link rel="icon" type="image/png" href="{{$site->favicon??''}}" />
        <link rel="icon" type="image/png" href="{{ config('app.photo_url').$site->image_folder}}favicon-16x16.png" sizes="16x16">
        <link rel="icon" type="image/png" href="{{ config('app.photo_url').$site->image_folder}}favicon-32x32.png" sizes="32x32">
        <link rel="icon" sizes="192x192" href="{{ config('app.photo_url').$site->image_folder}}android-chrome-192x192.png">
        <link rel="icon" sizes="512x512" href="{{ config('app.photo_url').$site->image_folder}}android-chrome-512x512.png">
        <link rel="apple-touch-icon" href="{{ config('app.photo_url').$site->image_folder}}apple-touch-icon.png">
    
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
    </head>
    <body>
        <div class="font-sans text-gray-900 antialiased">
            {{ $slot }}
        </div>
        <x-footer></x-footer>

    </body>
</html>
