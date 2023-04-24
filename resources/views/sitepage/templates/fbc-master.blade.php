<!DOCTYPE html>
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
   <link rel="shortcut icon" type="image/x-icon" href="{{ $site->favicon }}">
   {!!  $masterPage->css !!}
   {!!  $masterPage->js !!}
   <script data-turbolinks-eval="false">
      //<![CDATA[
      
      
            window.$ = window.$c;
      
            $(window).load(function() {
              window.initialLoad = true;
            });
      
            $(document).ready(function() {
              $('body').addClass('browser-' + bowser.name.toLowerCase().replace(' ', '-'))
              if (bowser.ios) $('body').addClass('os-ios')
              if (navigator.platform === 'Win32') $('body').addClass('os-windows')
      
              
              $(document).trigger('page:load');
            });
      
            $(document).on('page:load', function(e) {
              e.objectId = '141799'
              e.objectType = 'page'
              e.title = 'PAGE_SLUG'
      
              if(window.loaded) {
                Sites.vent.trigger("pageunload", e)
              }
      
              Sites.vent.trigger("pageload", e);
              // window.loaded is also used to ensure greenhouse loaded
              // and to retry if it failed.
              window.loaded = true;
            });
      
            $(document).on("page:before-change", function(e) {
              Sites.vent.trigger("pagechange", e);
            })
      
            $(document).on("page:before-unload", function(e) {
              window.loaded = false
              Sites.vent.trigger("pageunload", e);
            })
      
      
      //]]>
   </script>
   <script data-turbolinks-eval="false">
      //<![CDATA[
      
            window.$ = window.$c;
      
      //]]>
   </script>
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
                     <li class="">
                        <a href="/page/Sermons"><span>More Sermons</span></a>
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
                        <img srcset="https://images.prasso.io/fbc/cdn.files/images/site-images/5d8fac1e-955e-447c-96a6-24c140c639b1.png, https://images.prasso.io/fbc/cdn.files/images/site-images/5d8fac1e-955e-447c-96a6-24c140c639b1@2x.png" border="0">
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
                     <li class="">
                        <a href="/page/Sermons"><span>More Sermons</span></a>
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
                  <div class="photo-content editable photo-0 " data-id="12570587" data-category="photo" data-width="500" data-height="190">
                     <div class="aspect-helper" style="padding-top:38.0%"></div>
                     <div class="photo-container">
                        <img srcset="https://images.prasso.io/fbc/cdn.files/images/site-images/5ccfb98c-594e-492f-a7a4-2e96f5a28b33.png, https://images.prasso.io/fbc/cdn.files/images/site-images/5ccfb98c-594e-492f-a7a4-2e96f5a28b33@2x.png" border="0">
                     </div>
                  </div>
                  <div class="text-content text-1 editable" data-id="155629" data-category="text">
                     <div>
                        <p style="font-weight: 700; font-size: 1.6277em;">Faith Baptist Church  ~  Lake City, Florida</p>
                        <p style="font-weight: 400; font-size: 1.3885em;">Visit us on Faith Road behind The Home Depot, Aldi's, and Ashley Furniture</p>
                        <p style="font-weight: 400; font-size: 1.3885em;"><a href="https://www.facebook.com/FaithLakeCity" data-location="external" data-detail="https://www.facebook.com/FaithLakeCity/" data-category="link" target="_blank" class="cloverlinks"><span data-socialicon="roundedfacebook"><span class="socialIconSymbol" aria-hidden="true"></span><span class="sr-only">roundedfacebook</span></span></a>   <span data-socialicon="roundedinstagram"><span class="socialIconSymbol" aria-hidden="true"></span><span class="sr-only">roundedinstagram</span></span>   <span data-socialicon="roundeditunes"><span class="socialIconSymbol" aria-hidden="true"></span><span class="sr-only">roundeditunes</span></span>  
                           <a href="https://www.youtube.com/channel/UChYu0xZ0ZSQC2Y8CM4jv0ow/" data-location="external" data-detail="https://www.youtube.com/channel/UChYu0xZ0ZSQC2Y8CM4jv0ow/" data-category="link" target="_blank" class="cloverlinks"><span data-socialicon="roundedyoutube"><span class="socialIconSymbol" aria-hidden="true"></span><span class="sr-only">roundedyoutube</span></span></a> </p>
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
   <script>
      //<![CDATA[
      
            // initialize all slick slideshows on ready
            $(document).ready(function() {
              SlickInterface.reinitializeSlideshows()
            })
      
            $(document).on('page:before-unload', function() {
              // Prevent slideshow state sometimes transferring to another page in Bloom.
              SlickInterface.pauseSlideshows()
            })
      
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
</body>
</html>
