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
        <link rel="icon" type="image/png" href="{{$site->favicon??''}}" />
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
            header nav {height: 50px;margin:0; padding:3px; background:{{ $site->getNavBackgroundFromMainColor() }}; border: 1px solid {{ $site->getBorderColorFromMainColor() }};}
            nav ul li a {color:#212429;}
            nav ul li {padding:10px;font-weight: 900;}
            nav {min-height: 50px;margin:0; padding:3px; background: {{ $site->getNavBackgroundFromMainColor() }};border: 1px solid {{ $site->getBorderColorFromMainColor() }};}

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

    </head>
    <body>
        <div class="p-12 bg-white col-span-12 items-center ">
            <header class="flex justify-between items-center">
                <div class="relative col-span-3 left-0">
                @if ($site->logo_image)
                    <img src="{{ $site->logo_image }}" alt="{{ $site->site_name }}"  class="block h-9 w-auto" />
                @endif
                </div>
                <div class="max-w-xs p-0 m-auto">
                @if ($sitePage->section != "Dashboard")
                    <nav>
                        <ul>
                        {!! $site->getSiteMapList() !!}
                        </ul>
                    </nav>
                @endif
                </div>
                <div class="relative col-span-12  right-0 flex px-4">
                        <div class="flex-shrink-0">
                        <img class="h-10 w-10 rounded-full" src="{{ Auth::user()->getProfilePhoto() }}" alt="{{ Auth::user()->name }}" />
                        </div>
                        <div class="ml-3">
                            <div class="font-medium text-base text-gray-800"><a href="/user/profile">{{ Auth::user()->name }}</a></div>
                            <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
            </header>
            <div class="font-sans text-gray-900 antialiased">
                {{Session::get('message')}}
                {!!  $sitePage->description !!}
            </div>
        
        </div>
        <!-- COPYRIGHT -->
              
    </body>
</html>
