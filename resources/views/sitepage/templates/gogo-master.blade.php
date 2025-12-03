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
            .teambutton, button, [type="button"], [type="reset"], [type="submit"]{color:#f1f1f1;background-color:{{ $site->main_color }}; padding: 6px 3px 6px 3px ;border-radius: 5px;margin: 20px;}
            @media (max-width: 640px) {
                    nav {
                        display: none;
                    }

                    nav.hidden, div.hidden {
                        display: block;
                    }
                    
                }
            </style>
        

        @include('components.livewire-component-load')
    
        @livewireStyles
    
    </head>
    <body>
        <div class="p-3 bg-white    ">
        <header class="bg-white shadow">
            <div class="gjs-row" style="max-height:75px !important;">
                @if (Auth::user()!=null)
                <div class="gjs-cell" style="flex-basis:8.33%">
                    <div>
                        <img class="h-10 w-10 rounded-full max-w-full" src="{{ Auth::user()->getProfilePhoto() }}" alt="{{ Auth::user()->name }}" />
                    </div>
                    <div>
                        <div class="font-medium text-base text-gray-800"><a href="/user/profile">{{ Auth::user()->name }}</a></div>
                        <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                    </div>
                </div>
                @endif
     
                <div style="flex-basis:8.33%; position: relative;">
                    <button id="menu-toggle" class="sm:block sm:hidden " style="margin:0; flex-basis:8.33%;">
                        <svg class="h-6 w-6 fill-current text-gray-600" viewBox="0 0 24 24">
                            <path v-if="showMenu" fill-rule="evenodd" clip-rule="evenodd" d="M4 6h16v2H4V6zm0 5h16v2H4v-2zm0 5h16v2H4v-2z"/>
                            <path v-else fill-rule="evenodd" clip-rule="evenodd" d="M4 6h16v2H4V6zm0 7h16v2H4v-2zm0 7h16v2H4v-2z"/>
                        </svg>
                    </button>
                    <nav id='sm' class="sm:hidden sm:block" style="position: absolute; top: 50px; left: -70px; z-index: 999; height: 350px; overflow-y: auto;">
                        <ul>
                            {!! $site->getSiteMapList() !!}
                        </ul>
                    </nav>
                </div>
            </div>
            <div class="gjs-row sm:block sm:hidden ">
                @if ($site->logo_image)
                    <img src="{{ $site->logo_image }}" alt="{{ $site->site_name }}"  class="block h-9 m-auto" />
                @endif
                </div>
            <div class="p-0 m-auto" x-data="{ show: true }" x-show="show && window.innerWidth >= 640">
            @if ($site->logo_image)
                    <img src="{{ $site->logo_image }}" alt="{{ $site->site_name }}" style="position:relative; top:-15px; left:0;" class="block h-9" />
                @endif    
                <div class="p-0 ml-10 m-auto  border border-solid border-gray-500">
                @if ($sitePage->section != "Dashboard")
                    <nav id='lg' class="w-3/4 ml-10 lg:block lg:hidden">
                        <ul>
                        {!! $site->getSiteMapList() !!}
                        </ul>
                    </nav>
                @endif
                </div>
                </div>
                
        </header>
           
            <div class="clear-both font-sans text-gray-900 antialiased">
            @if (session()->has('message'))
                <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md my-3 mb-0" role="alert">
                    <div>
                        <p class="m-auto text-center text-sm">{{ session('message') }}</p>
                    </div>
                </div>
                @endif
                {!! $sitePage->description !!}
            </div>
        
            <script>
                const menuToggle = document.getElementById('menu-toggle');
                const nav = document.querySelector('nav');

                menuToggle.addEventListener('click', () => {
                    nav.classList.toggle('hidden');
                });
            </script>
        </div>
        <!-- COPYRIGHT -->
        
        <!-- PWA Hidden Login Access & Service Worker Registration -->
        <script src="{{ asset('/js/pwa-login.js') }}" defer></script>
        
        <!-- PWA Install Prompt (Android & iOS) -->
        <script src="{{ asset('/js/pwa-install-prompt.js') }}" defer></script>
              
   @livewireScripts
    </body>
</html>
