<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" type="image/png" href="{{$site->favicon??''}}" />
        <title>{{ config('app.name', 'Prasso') }} - {{ $title }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        @livewireStyles
    
        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.6.0/dist/alpine.js" defer></script>
    
        <script type="text/javascript"
                src="https://prasso.outseta.com/Scripts/client/dist/outseta.nocode.widget.min.js">
        </script>
        <script type="text/javascript"
                src="https://prasso.outseta.com/Scripts/client/dist/outseta.auth.widget.min.js"
                data-popup-selector="a[href^='https://prasso.outseta.com/widgets/auth']"
                defer>
        </script>
        <script type="text/javascript"
                src="https://prasso.outseta.com/Scripts/client/dist/outseta.profile.widget.min.js"
                data-popup-selector="a[href^='https://prasso.outseta.com/widgets/profile']"
                defer>
        </script>
    
    
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @livewire('navigation-dropdown')

            <!-- Page Heading -->
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

        <x-footer></x-footer>

        @stack('modals')

        @livewireScripts
    
    </body>
</html>
