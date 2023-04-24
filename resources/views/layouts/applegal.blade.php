<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{$site->favicon??''}}" />
    <title>{{ $site->site_name }} - {{ $title??'' }}</title>

    @if (isset($masterPage))
    {!!  $masterPage->css !!}
    {!!  $masterPage->js !!}
    @endif

    @livewireStyles

    <link rel="icon" type="image/png" href="{{ config('app.photo_url').$site->image_folder}}favicon.ico">
    <link rel="icon" type="image/png" href="{{ config('app.photo_url').$site->image_folder}}favicon-16x16.png" sizes="16x16">
    <link rel="icon" type="image/png" href="{{ config('app.photo_url').$site->image_folder}}favicon-32x32.png" sizes="32x32">
    <link rel="icon" sizes="192x192" href="{{ config('app.photo_url').$site->image_folder}}android-chrome-192x192.png">
    <link rel="icon" sizes="512x512" href="{{ config('app.photo_url').$site->image_folder}}android-chrome-512x512.png">
    <link rel="apple-touch-icon" href="{{ config('app.photo_url').$site->image_folder}}apple-touch-icon.png">

    <style>{{$site->app_specific_css}}</style>

</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
         <img style="max-width:150px;" src=config('constants.cloudfront_asset_url')."/prasso/prasso_logo.png" alt="Prasso" />
</div>
            @if (isset($header))

            <!-- Page Heading -->
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
            @endif
            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            <x-footer></x-footer>


</body>

</html>