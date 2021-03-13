<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
      <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Mercy Full Farms - {{ $title }}</title>
  
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

        <meta name="description" content="Housing- Emergency shelter for homeless and low cost housing during rehabilitation.
Housing and care for special needs young adults who have aged out of the foster care system.">
       
        <!-- Apple Stuff -->
            <link rel="apple-touch-icon" href="https://www.mercyfullfarms.com/favicon.ico">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
            <meta name="apple-mobile-web-app-title" content="Mercy Full Farms">
        
        <!-- Google / Search Engine Tags -->
            <meta itemprop="name" content="Mercy Full Farms">
        <meta itemprop="description" content="Providing, Mentoring, Encouraging">
        <meta itemprop="image" content="https://www.mercyfullfarms.com/images/mercy_full_logo_200.png">
        
        <!-- MS Tile - for Microsoft apps-->
        <meta name="msapplication-TileImage" content="https://www.mercyfullfarms.com/images/mercy_full_logo_200.png">
        
        <!-- Facebook Meta Tags -->
        <meta property="og:type" content="website">
        <meta property="og:title" content="Mercy Full Farms">
        <meta property="og:description" content="Providing, Mentoring, Encouraging">
        <meta property="og:image" content="https://www.mercyfullfarms.com/images/mercy_full_logo_200.png">
        <meta property="og:url" content="https://www.facebook.com/mercyfullfarms">
        
        <!-- Twitter Meta Tags -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Mercy Full Farms">
        <meta name="twitter:description" content="Providing, Mentoring, Encouraging">
        <meta name="twitter:image" content="https://www.mercyfullfarms.com/images/mercy_full_logo_200.png">
        
            <link rel="icon" type="image/png" href="https://www.mercyfullfarms.com/favicon.ico">

            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"><link rel="stylesheet" href="https://grapedrop.com/css/gjs-base.css?id=d0383dd1e92b8b8ea83e">
        
        
        <script>
            window._formUrl = "https://grapedrop.com/form-collector/b3f0d2b2b42846cb8b0c4295d07a764d";
            window.__gRecapKey = "";
            window.postJQCnt = [];
            window.postJQ = function() {
                window._jqloaded = 1;
                postJQCnt.forEach(function(fn) { fn && fn() })
            }
        </script>
        <script async="" src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js" onload="postJQ()"></script>
        
            <script async="" src="https://grapedrop.com/js/gpd.js?id=941871ee8356f83232d3"></script>
        
        <style>
            body { margin: 0; padding: 0; overflow-x: hidden; }
        </style>
        <style data-css-anim="">
            [data-anim-type]:not([data-anim-done]) { opacity: 0; }
        </style>
        
            </head>
        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <style data-css-anim="">
            [data-anim-type]:not([data-anim-done]) { opacity: 0; }
        </style>
        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.2.1/dist/alpine.js" defer></script>
    </head>
    <body>
        <div class="font-sans text-gray-900 antialiased">
            {{ $slot }}
        </div>
        <x-footer></x-footer>

    </body>
</html>
