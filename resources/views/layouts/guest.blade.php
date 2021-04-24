<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Prasso') }} - {{ $title }}</title>

        <script src="https://tilda.cc/js/jquery-1.10.2.min.js"  type="text/javascript"></script>

<script src="https://static.tildacdn.com/js/tilda-scripts-3.0.min.js"  type="text/javascript"></script>

<link href="https://tilda.cc/css/tilda-grid-3.0.min.css" rel="stylesheet" media="screen">

<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&subset=latin,cyrillic" rel="stylesheet">		

<link href="https://tilda.cc/projects/style/?projectid=4017550" rel="stylesheet" media="screen">
<script src="https://tilda.cc/projects/js/?projectid=4017550"></script>


        <script src="https://static.tildacdn.com/js/tilda-animation-ext-1.0.min.js"></script>
    <script src="https://static.tildacdn.com/js/tilda-animation-sbs-1.0.min.js"></script>

<script type="text/javascript">
    window.ver='67245';
</script>

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
    <body>
        <div class="font-sans text-gray-900 antialiased">
            {{ $slot }}
        </div>
        <x-footer></x-footer>

    </body>
</html>
