<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" type="image/png" href="{{asset('assets/img/favicon.ico')}}" />
        <title>{{ config('app.name', 'Prasso') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        @livewireStyles

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.6.0/dist/alpine.js" defer></script>
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

        </div>
            <div class="bg-white shadow ">
            <div class="p-14 flex sm:justify-center items-center  ">
            <a class="pr-1" href="contact">contact</a> | <a href="privacy">privacy</a> | <a href="terms">terms</a>
            </div>
            <div class="flex sm:justify-center items-center sm:pt-0 ">
            <small>&copy faxt 1999-2020</small>
            </div>
        </div> 

        @stack('modals')

        @livewireScripts
    </body>
</html>
