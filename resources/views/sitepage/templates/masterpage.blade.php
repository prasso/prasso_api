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
        
        <!-- PWA Manifest - Dynamic per site -->
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="{{ $site->main_color ?? '#000000' }}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="{{ $site->site_name ?? config('app.name') }}">
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        @if (isset($masterPage))
        {!!  $masterPage->js !!}
        {!!  $masterPage->css !!}
        @else
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <link href="/js/google-fonts-inter.css" rel="stylesheet">
         <link href="/js/google-fonts-material-icon.css" rel="stylesheet">
         <link rel="stylesheet" href="/js/google-material-design-iconic-font.2.2.0.min.css">
         <link href="/js/google-fonts-Roboto.css" rel="stylesheet"> 
         <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">
         <!-- Scripts -->
         @include('components.alpine-loader')
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
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <style>
            body {
            font-family: 'Open Sans', sans-serif;
            color: #333;
            background-color: #f1f1f1;
            line-height: 1.5;
            margin:20px;
            }
                .testimonial {
            border: 1px solid #ccc;
            padding: 20px;
            margin: 20px 0;
            }

            .testimonial p:first-of-type {
            font-style: italic;
            }

            .testimonial p:last-of-type {
            font-size: 0.8em;
            color: #666;
            }
            header {  align-items: center;}

            header img {
            float: left;
            position: relative;
            top: -20px;
            margin: 20px;
            }

            header h1 {
            font-size: 2em;
            }
            .hero {
            background-color: #aaa;
            color: #fff;
            padding: 150px 0;
            text-align: center;
            }
            .hero h1 {
            font-size: 48px;
            margin: 0;
            }
            .hero p {
            font-size: 24px;
            margin: 0;
            text-align: center;
            color:#184594;
            max-width: 100%;
            }
            p {
            font-size: 18px;
            padding: 2%;
            text-align:center;

            }
            
            header nav ul li {float:right; padding:10px;font-weight: 900;}
            
            header nav {padding:3px; }
            nav ul li a {color:#212429;}
            nav ul li {padding:10px;font-weight: 900;}
            nav {min-height: 50px;margin:0; padding:3px;}

            section {clear:both; border-bottom: solid 1px #184594; padding: 20px; margin: 20px 0;}
            section h2 {color:{{ $site->main_color }};font-size: 36px; text-align:center; margin: 0; padding: 5px;}
            section ul{text-align:center;}
            section table{text-align:center;  margin-left: auto;
            margin-right: auto;}
            #about {background-color: #fff;}
            #features {background-color: #fff;}
            #pricing {background-color: #fff;}
            #testimonials {background-color: #fff;}
            #signup {background-color: #fff;}
            teambutton, button, [type="button"], [type="reset"], [type="submit"]{color:#f1f1f1;background-color:{{ $site->main_color }}; padding: 6px 3px 6px 3px ;border-radius: 5px;margin: 20px;}
            </style>
        
            @include('components.livewire-component-load')
    
            @livewireStyles
   
    </head>
    <body>
        <div class="p-12 bg-white col-span-12 items-center ">
            <header class="flex justify-between items-start">
                <div class="relative col-span-3 left-0">
                @if ($site->logo_image)
                    <img src="{{ $site->logo_image }}" alt="{{ $site->site_name }}"  class="block h-9 w-auto" />
                @endif
                </div>

<!-- new nav with dropdown -->
<div class="p-4 border border-solid border-gray-500">
@if ($sitePage->section != "Dashboard")
    <nav id="lg" class="overflow-ellipsis w-full">
        <!-- Mobile Navigation -->
        <div class="lg:hidden">
            <button id="mobile-menu-btn" class="flex items-center px-3 py-2 border rounded text-gray-600 border-gray-600 hover:text-gray-800 hover:border-gray-800 focus:outline-none focus:text-gray-800 focus:border-gray-800 transition duration-150 ease-in-out">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
            <div id="mobile-menu" class="hidden bg-gray-100 mt-2">
                <ul>
                {!! $site->getSiteMapList() !!}
                 <!--   <li><a class="block pl-3 pr-4 py-2 text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 transition duration-150 ease-in-out" href="/page/Welcome-OLDPRASSO">Prasso - application framework</a></li>
                 -->   
                </ul>
            </div>
        </div>
        
        <!-- Desktop Navigation -->
        <ul class="hidden lg:flex">
            {!! $site->getSiteMapList() !!}
          <!--   <li><a class="block px-3 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out" href="/page/Welcome-OLDPRASSO">Prasso - application framework</a></li>
           -->
        </ul>
    </nav>
    @endif
</div>


                <!-- old
                <div class="p-0 ml-10 m-auto  border border-solid border-gray-500">
                @if ($sitePage->section != "Dashboard")
                    <nav id='lg' class="overflow-ellipsis w-3/4 ml-10">
                        <ul>
                        {!! $site->getSiteMapList() !!}
                        </ul>
                    </nav>
                @endif
                </div>
                -->

                @if (Auth::user()!=null)
                <div class="relative col-span-12  right-0 flex px-4">
                        <div class="flex-shrink-0">
                        <img class="h-10 w-10 rounded-full" src="{{ Auth::user()->getProfilePhoto() }}" alt="{{ Auth::user()->name }}" />
                        </div>
                        <div class="ml-3">
                            <div class="font-medium text-base text-gray-800"><a href="/user/profile">{{ Auth::user()->name }}</a></div>
                            <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                @endif
            </header>
            <div class="font-sans text-gray-900 antialiased">
            @if (session()->has('message'))
                <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md my-3 mb-0" role="alert">
                    <div>
                        <p class="m-auto text-center text-sm">{{ session('message') }}</p>
                    </div>
                </div>
                @endif
                {!! $sitePage->description !!}
            </div>
        
        </div>
        <!-- COPYRIGHT -->
        <script>
    // Toggle mobile menu visibility
    document.getElementById('mobile-menu-btn').addEventListener('click', function() {
        document.getElementById('mobile-menu').classList.toggle('hidden');
    });
</script>

        <!-- PWA Hidden Login Access & Service Worker Registration -->
        <script src="{{ asset('/js/pwa-login.js') }}" defer></script>
        
        <!-- PWA Install Prompt (Android & iOS) -->
        <script src="{{ asset('/js/pwa-install-prompt.js') }}" defer></script>

@livewireScripts
    </body>
</html>
