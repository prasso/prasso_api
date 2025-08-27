<!DOCTYPE html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <meta name="csrf-token" content="{{ csrf_token() }}">

   <title>{{ $site->site_name }} - {{ $sitePage->title??'' }}</title>
   <meta name="description" content="{{ $sitePage->title??'' }}" />
   <meta property="og:title" content="{{ $site->site_name }} - {{ $sitePage->title??'' }}" />
   <meta property="og:description" content="{{ $sitePage->title??'' }}" />
   <meta property="og:type" content="website" />
   <meta property="og:url" content="{{ $page_short_url }}" />
   <meta property="og:image" content="{{ $site->logo_image }}" />
   <meta name="twitter:card" content="summary_large_image" />
   <meta name="twitter:title" content="{{ $site->site_name }} - {{ $sitePage->title??'' }}" />
   <meta name="twitter:description" content="{{ $sitePage->title??'' }}" />
   <meta name="twitter:image" content="{{ $site->logo_image }}" />
   <link rel="icon" type="image/png" href="{{$site->favicon??''}}" />
   <link rel="icon" type="image/png" href="{{ config('app.photo_url').$site->image_folder}}favicon-16x16.png" sizes="16x16">
   <link rel="icon" type="image/png" href="{{ config('app.photo_url').$site->image_folder}}favicon-32x32.png" sizes="32x32">
   <link rel="icon" sizes="192x192" href="{{ config('app.photo_url').$site->image_folder}}android-chrome-192x192.png">
   <link rel="icon" sizes="512x512" href="{{ config('app.photo_url').$site->image_folder}}android-chrome-512x512.png">
   <link rel="apple-touch-icon" href="{{ config('app.photo_url').$site->image_folder}}apple-touch-icon.png">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="/js/faith-app.min.js"></script>
<script src="https://images.prasso.io/fbc/cdn.files/production/websites/designs/dusk/base-ae8948f5e23c447398a0e96992fcb396c6936d1bd5213c83a88a0134815f6158.js" debug="false" data-turbolinks-track="true" class="clover"></script>
<script>
//<![CDATA[

         var __REACT_ON_RAILS_EVENT_HANDLERS_RAN_ONCE__ = true

//]]>
</script>
<script src="https://images.prasso.io/fbc/cdn.files/js/runtime-29643ceddd61d164b25a.js"></script>
<script src="https://images.prasso.io/fbc/cdn.files/js/1-794656562a19776f9d49.chunk.js"></script>
<script src="https://images.prasso.io/fbc/cdn.files/js/2-a38190a685725a895f06.chunk.js"></script>
<script src="https://images.prasso.io/fbc/cdn.files/js/media-1ab6fa2937934576a72f.chunk.js"></script>
<link rel="stylesheet" media="all" href="https://images.prasso.io/fbc/cdn.files/production/websites/application-2d3ea95936f79d8cf68c4a91238720210c7d217a5301ad14816159e44f1ae032.css" data_turbolinks_track="true" debug="false" />
<link rel="stylesheet" media="all" href="https://images.prasso.io/fbc/cdn.files/production/websites/designs/dusk/base-9c40b38ce0ba7fbd608fa6f1889f31185b652733c22bdf7fa828349f50411476.css" debug="false" />
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" media="all" href="/css/fbc-colors.css" id="color-css" />
<link rel="stylesheet" media="all" href="https://images.prasso.io/fbc/cdn.files/_user_generated_stylesheets/published_fonts_1210c48d-3bc6-4059-b211-ccceae4a8d6a_275370be77490640d1a637c4ba2f42bf.css" id="font-css" />
<link rel="stylesheet" media="all" href="https://images.prasso.io/fbc/cdn.files/_user_generated_stylesheets/published_tweaks_1210c48d-3bc6-4059-b211-ccceae4a8d6a_74e0c2dd5e4fed4f834f88c72f6ba20d.css" id="tweak-css" />
<link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">
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


        <script data-turbolinks-eval="false">
    //<![CDATA[


    window.$ = window.$c;

    $(window).load(function () {
      window.initialLoad = true;
    });

    $(document).ready(function () {
      $('body').addClass('browser-' + bowser.name.toLowerCase().replace(' ', '-'))
      if (bowser.ios) $('body').addClass('os-ios')
      if (navigator.platform === 'Win32') $('body').addClass('os-windows')

      Sites.start({
      preview: false,
        greenhouse_preview: false,
        environment: "production",
        site_name: "faithlakecity",
        ssl: true,
        main_navigation: "more_button",
        mobile_navigation: "top-left",
        top_gallery: "",
        navigation_settings: { "main": "more_button", "mobile": "top-left", "hoverable_sub_navigation": true },
        gallery_settings: { "peek": true, "hide_gallery_arrows_while_editing": false },
        tabs_settings: { "style": "ribbon" },
        s3_bucket_name: "media.faxt.com/faith",
        base_domain: "faithlakecity.com",
        site_uuid: "1210c48d-3bc6-4059-b211-ccceae4a8d6a",
        content_prefix: "12/1210c48d-3bc6-4059-b211-ccceae4a8d6a",
        events_settings: { "has_sidebar": true, "always_show_description": true, "prefer_separate_date_and_time": true, "always_show_icons": true, "always_show_location": true, "always_show_details_via_list_item": true, "fixed_tiles_per_row": true, "deviant_list_item_buttons": true, "gallery": { "peek": false } },
        forms_settings: { "featured_image_floats_to_top": true },
        tabs_settings: { "style": "ribbon" },
        forms_domain: "https://faithlakecity.com",
        form_builder_embed_url: "/cdn.files/mb.formbuilder.embed.js",
        packs_graph: { "runtime": ["/js/runtime-29643ceddd61d164b25a.js", []], "1": ["/js/1-794656562a19776f9d49.chunk.js", []], "2": ["/js/2-a38190a685725a895f06.chunk.js", []], "7": ["/js/js/7-b879346cc33af45bdf54.chunk.js", []], "media": ["/js/media-1ab6fa2937934576a72f.chunk.js", ["runtime", "1", "2"]], "prayer": ["/js/prayer-8f35a7b9f5649e00c724.chunk.js", ["runtime", "1", "7"]], "small-groups": ["/js/small-groups-7479f4ab3e343196f256.chunk.js", ["runtime", "1", "2"]] }
      })

      $(document).trigger('page:load');
    });

    $(document).on('page:load', function (e) {
      e.objectId = '24030'
      e.objectType = 'page'
      e.title = "{{ $site->site_name }} - {{ $sitePage->title??'' }}" 

      if (window.loaded) {
        Sites.vent.trigger("pageunload", e)
      }

      Sites.vent.trigger("pageload", e);
      // window.loaded is also used to ensure greenhouse loaded
      // and to retry if it failed.
      window.loaded = true;
    });

    $(document).on("page:before-change", function (e) {
      Sites.vent.trigger("pagechange", e);
    })

    $(document).on("page:before-unload", function (e) {
      window.loaded = false
      Sites.vent.trigger("pageunload", e);
    })


//]]>
  </script>
    <!-- Include the Livewire AJAX loader script -->
    @include('components.livewire-component-load')
  <script data-turbolinks-eval="false">
    //<![CDATA[

    window.$ = window.$c;

//]]>
  </script>

@livewireStyles

</head>
<body class="palette nav-children-expand tabs-style-ribbon first-subpalette1 last-subpalette1 footer-subpalette4 dusk">
   <!-- site content -->
   <div id="wrapper">
      <a class="skip-link" href="#sections">Skip to main content</a>
      <!-- mobile nav -->
      <div id="mobile-nav-button-container">
         <button id="mobile-nav-button" class="nav-menu-button sites-button">
            <span class="text">Menu</span>
            <span class="mobile-nav-icon">
               <span class="first"></span>
               <span class="middle"></span>
               <span class="last"></span>
            </span>
         </button>
      </div>
      <div id="mobile-navigation">
         <nav class="main-navigation">
            <button class="nav-menu-button">
            <span class="text">Menu</span>
            <span class="mobile-nav-icon">
            <span class="first"></span>
            <span class="middle"></span>
            <span class="last"></span>
            </span>
            </button>
            <ul>
               <li class=" first landing">
                  <a href="/page/Welcome">
                  <span>Home</span>
                  </a>
               </li>
               <li class=" has-sub">
                  <a href="/page/About">
                  <span>About</span>
                  <i></i>
                  </a>
                  <ul class="sub-navigation">
                     <li class="">
                        <a href="/page/About"><span>Worship Services</span></a>
                     </li>
                     <li class="">
                        <a href="/page/WhatWeBelieve"><span>What We Believe</span></a>
                     </li>
                     <li class="">
                        <a href="/page/OurPastors"><span>Our Pastors</span></a>
                     </li>
                  </ul>
               </li>
               <li class=" has-sub">
                  <a href="/page/Connect">
                  <span>Connect</span>
                  <i></i>
                  </a>
                  <ul class="sub-navigation">
                     <li class="">
                        <a href="/page/Connect"><span>Find Us</span></a>
                     </li>
                     <li class="">
                        <a href="/page/ContactUs"><span>Contact Us</span></a>
                     </li>
                  </ul>
               </li>
               <li class=" has-sub">
                  <a href="/page/RecentSermons">
                  <span>Sermons</span>
                  </a>
                  <ul class="sub-navigation">
                     <li class="">
                        <a href="/page/Livestream"><span>Live Services</span></a>
                     </li>
                     <li class="">
                        <a href="/page/RecentSermons"><span>Recent Sermons</span></a>
                     </li>
                     
                  </ul>
               </li>
               <li class=" last landing">
                  <a href="/page/Donate">
                  <span>Give</span>
                  </a>
               </li>
            </ul>
         </nav>
      </div>
      <!-- page content -->
      <section id="main-content" class="clearfix">
         <header class="header site-section clearfix first-section-subpalette1" data-id="39510" data-category="header">
            <div class="content-wrapper">
               <div class="branding media-content">
                  <div class="photo-content editable photo-0 " data-id="155630" data-category="photo" data-width="500" data-height="190">
                     <div class="aspect-helper" style="padding-top:38.0%"></div>
                     <div class="photo-container">
                        <img src="https://images.prasso.io/faith/FaithLakeCityLogo-250.png" border="0">
                     </div>                                                                                                             
                  </div>
               </div>
               <nav id="main-navigation" class="main-navigation clearfix">
                  <ul>
                     <li class="landing first">
                        <a href="/page/Welcome"><span>Home</span></a>
                     </li>
                     <li class=" has-sub">
                        <a href="/page/About"><span>About</span></a>
                        <ul class="sub-navigation">
                           <li>
                              <a href="/page/About"><span>Worship Services</span></a>
                           </li>
                           <li>
                              <a href="/page/WhatWeBelieve"><span>What We Believe</span></a>
                           </li>
                           <li>
                              <a href="/page/OurPastors"><span>Our Pastors</span></a>
                           </li>
                        </ul>
                        <i></i>
                     </li>
                     <li class=" has-sub">
                        <a href="/page/Connect"><span>Connect</span></a>
                        <ul class="sub-navigation">
                           <li>
                              <a href="/page/Connect"><span>Find Us</span></a>
                           </li>
                           <li>
                              <a href="/page/ContactUs"><span>Contact Us</span></a>
                           </li>
                        </ul>
                        <i></i>
                     </li>
                     <li class=" has-sub">
                        <a href="/page/RecentSermons">
                        <span>Sermons</span>
                        </a>
                        <ul class="sub-navigation">
                        <li class="">
                        <a href="/page/Livestream"><span>Live Services</span></a>
                     </li>
                     <li class="">
                        <a href="/page/RecentSermons"><span>Recent Sermons</span></a>
                     </li>
                        </ul>
                     </li>
                     <li class="landing last">
                        <a href="/page/Donate"><span>Give</span></a>
                     </li>
                  </ul>
               </nav>
            </div>
         </header>
         <main role="main" id="sections">
            {{Session::get('message')}}
            {!! $sitePage->description !!}
         </main>
         <footer id="footer-39509" class="site-section subpalette4 footer editable " data-id="39509" data-category="footer">
            <div class="bg-helper">
               <div class="bg-opacity" style="opacity: 0.0"></div>
            </div>
            <div class="content-wrapper clearfix">
               <div class="group group-0">
                  <div style="display: flex; align-items: flex-start;">
                        <img style="position:absolute; top:0px;max-width:250px" src="https://images.prasso.io/faith/FaithLakeCityLogo-250.png" border="0">
                  </div>
                  <div class="text-content text-1 editable" data-id="155629" data-category="text">
                     <div>
                        <p style="font-weight: 700; font-size: 1.6277em;line-height: 1.2;">Faith Baptist Church</p>
                        <div>
                           <p style="color: #666666;font-weight: 200; font-size: 1.3885em; line-height: 1.2;">299 SW Faith Road</p>
                           <p style="color: #666666;font-weight: 200; font-size: 1.3885em; line-height: 1.2;">Lake City, FL 32025-1317</p>
                           <p style="color: #666666;font-weight: 200; font-size: 1.3885em;line-height: 3;">Visit us on Faith Road behind The Home Depot and Aldi's</p>
                        </div>
                        <p style="font-weight: 400; font-size: 1.3885em;"><a href="https://www.facebook.com/FaithLakeCity" data-location="external" data-detail="https://www.facebook.com/FaithLakeCity/" data-category="link" target="_blank" class="cloverlinks"><span data-socialicon="roundedfacebook"><span class="socialIconSymbol" aria-hidden="true"></span><span class="sr-only">roundedfacebook</span></span></a>   <span data-socialicon="roundedinstagram"><span class="socialIconSymbol" aria-hidden="true"></span><span class="sr-only">roundedinstagram</span></span>   <span data-socialicon="roundeditunes"><span class="socialIconSymbol" aria-hidden="true"></span><span class="sr-only">roundeditunes</span></span>  
                           <a href="https://www.youtube.com/@faithlakecity/streams/" data-location="external" data-detail="https://www.youtube.com/@faithlakecity/streams/" data-category="link" target="_blank" class="cloverlinks"><span data-socialicon="roundedyoutube"><span class="socialIconSymbol" aria-hidden="true"></span><span class="sr-only">roundedyoutube</span></span></a> </p>
                     </div>
                  </div>
                  <div class="text-content text-2 editable" data-id="12968895" data-category="text">
                     <div>
                        <p><br></p>
                     </div>
                  </div>
               </div>
            </div>
         </footer>
      </section>
   </div>
   <script>
      //<![CDATA[
      window.gon={};gon.parent_slug="sermons";gon.page_slug=null;gon.current_page_id=141799;gon.design_name="dusk";
      //]]>
   </script>
 
   <!-- Google Analytics -->
   <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
      
      ga('create', 'UA-101161806-1', 'auto');
      ga('send', 'pageview');
      
   </script>
   @livewireScripts
</body>
</html>
