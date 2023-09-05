<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>{{ config('app.name', $site->title) }} - Visual Editor</title>  
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{$site->favicon}}" />
    <meta content="Prasso uses Grapesjs" name="description">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.css">
    <link rel="stylesheet" href="/css/grapes.min.css?v0.21.1">
    <link rel="stylesheet" href="/css/grapesjs-preset-webpage.min.css">
    <link rel="stylesheet" href="/css/tooltip.css">
    <link rel="stylesheet" href="/css/demos.css?v3">
    <link href="https://unpkg.com/grapick/dist/grapick.min.css" rel="stylesheet">
  

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
    <script src="/js/grapes.min.js?v0.21.4"></script>
    <script src="https://unpkg.com/grapesjs-preset-webpage@1.0.2"></script>
    <script src="https://unpkg.com/grapesjs-blocks-basic@1.0.1"></script>
    <script src="https://unpkg.com/grapesjs-plugin-forms@2.0.5"></script>
    <script src="https://unpkg.com/grapesjs-component-countdown@1.0.1"></script>
    <script src="https://unpkg.com/grapesjs-plugin-export@1.0.11"></script>
    <script src="https://unpkg.com/grapesjs-tabs@1.0.6"></script>
    <script src="https://unpkg.com/grapesjs-custom-code@1.0.1"></script>
    <script src="https://unpkg.com/grapesjs-touch@0.1.1"></script>
    <script src="https://unpkg.com/grapesjs-parser-postcss@1.0.1"></script>
    <script src="https://unpkg.com/grapesjs-tooltip@0.1.7"></script>
    <script src="https://unpkg.com/grapesjs-tui-image-editor@0.1.3"></script>
    <script src="https://unpkg.com/grapesjs-typed@1.0.5"></script>
    <script src="https://unpkg.com/grapesjs-style-bg@2.0.1"></script>
    @if (isset($site->app_specific_css) && str_starts_with($site->app_specific_css, 'http') )
            <link rel="stylesheet" href="{{$site->app_specific_css}}">
        @else
            <style>{!! $site->app_specific_css !!}</style>
        @endif
    @if (isset($masterPage))
    {!!  $masterPage->js !!}
    {!!  $masterPage->css !!}
    @endif
  
    <link rel="stylesheet" href="/css/grapes-component.css">
  </head>
  <body>
  <form id="sitePageForm" action="/save-site-page" method="post">
        <input type="hidden" name="id" value="{{$sitePage->id}}"/>
        <input type="hidden" name="fk_site_id" value="{{$sitePage->fk_site_id}}"/>
        <input type="hidden" name="section" value="{{$sitePage->section}}"/>
        <input type="hidden" name="title" value="{{$sitePage->title}}"/>
        <input type="hidden" id="page_data" name="description" value="{{$sitePage->description}}"/>
        <input type="hidden" name="url" value="{{$sitePage->url}}"/>
        <input type="hidden" name="csrf-token" value="{{ csrf_token() }}" />
        <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
        <input type="hidden" name="headers" value="{{ $sitePage->headers }}" />
        <input type="hidden" name="masterpage" value="{{ $sitePage->masterpage }}" />
        <input type="hidden" name="login_required" value="{{ $sitePage->login_required }}" />
        <input type="hidden" name="user_level" value="{{ $sitePage->user_level }}" />
        <input type="hidden" name="template" value="{{ $sitePage->template }}" />
        <input type="hidden" id="page_style" name="style" value="{{ $sitePage->style }}" />
        <input type="hidden" id="where_value" name="where_value" value="{{ $sitePage->where_value }}" />
        
  </form>

    <div id="gjs" style="height:0px; overflow:hidden">
    
    </div>

    
    <script type="text/javascript">
      const userIsAdmin = {{Auth::user()->hasRole('1') ? 'true' : 'false'}};
      const page_id = {{$sitePage->id}};
    </script>
    <script type="module" src="/js/grapes-visual-editor/grapes-component.js"></script> 
  </body>
</html>