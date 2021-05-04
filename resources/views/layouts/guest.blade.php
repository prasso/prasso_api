<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Prasso') }} - {{ $title }}</title>

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
