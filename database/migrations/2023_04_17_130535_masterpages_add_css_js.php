<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MasterpagesAddCssJs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('masterpages', function (Blueprint $table) {
            $table->text('css')
                    ->after('description')
                    ->nullable();
            $table->text('js')
                    ->after('description')
                    ->nullable();
        });
        
        //update data in existing masterpage records
        DB::table('masterpages')
                ->where('id', 1)
                ->update(['js' => '<script src="/js/jquery1.10.0.min.js"></script> 
                <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.2.1/dist/alpine.js" defer></script>',
                'css' => '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
                <link rel="stylesheet" href="/css/app.css">
                <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">
                        <link href="/js/google-fonts-inter.css" rel="stylesheet">
                <link href="/js/google-fonts-material-icon.css" rel="stylesheet">
                <link rel="stylesheet" href="/js/google-material-design-iconic-font.2.2.0.min.css">
                <link href="/js/google-fonts-Roboto.css" rel="stylesheet"> 
                <script src="/js/jqueryui.1.12.1.min.css"></script>'
                ]);

                DB::table('masterpages')
                ->where('id', 2)
                ->update(['js' => '<script src="/js/jquery1.10.0.min.js"></script> 
                        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.2.1/dist/alpine.js" defer></script>',
                        'css' => '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
                        <link rel="stylesheet" href="/css/app.css"> 
                        <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">
                                <link href="/js/google-fonts-inter.css" rel="stylesheet">
                        <link href="/js/google-fonts-material-icon.css" rel="stylesheet">
                        <link rel="stylesheet" href="/js/google-material-design-iconic-font.2.2.0.min.css">
                        <link href="/js/google-fonts-Roboto.css" rel="stylesheet"> 
                        <script src="/js/jqueryui.1.12.1.min.css"></script>'
                ]);
         
                DB::table('masterpages')
                ->where('id', 3)
                ->update(['css' => ' <link rel=\"stylesheet\" media=\"all\" href=\"https://images.prasso.io/fbc/cdn.files/production/websites/application-2d3ea95936f79d8cf68c4a91238720210c7d217a5301ad14816159e44f1ae032.css\" data_turbolinks_track=\"true\" debug=\"false\" />
                        <link rel=\"stylesheet\" media=\"all\" href=\"https://images.prasso.io/fbc/cdn.files/production/websites/designs/dusk/base-9c40b38ce0ba7fbd608fa6f1889f31185b652733c22bdf7fa828349f50411476.css\" debug=\"false\" />  
                        <link rel="preconnect" href="https://fonts.gstatic.com">
                        <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
                        <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
                        <link rel="stylesheet" media="all" href="/css/fbc-colors.css" id="color-css" />
                        <link rel="stylesheet" media="all" href="https://images.prasso.io/fbc/cdn.files/_user_generated_stylesheets/published_fonts_1210c48d-3bc6-4059-b211-ccceae4a8d6a_275370be77490640d1a637c4ba2f42bf.css" id="font-css" />
                        <link rel="stylesheet" media="all" href="https://images.prasso.io/fbc/cdn.files/_user_generated_stylesheets/published_tweaks_1210c48d-3bc6-4059-b211-ccceae4a8d6a_74e0c2dd5e4fed4f834f88c72f6ba20d.css" id="tweak-css" />
                        '  ,
                        'js' => '<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
                        <script src="https://images.prasso.io/fbc/cdn.files/production/websites/application-31874a24645c9b67af7b8706e47524e06df702f3fe569a8a0eb69d396abd4ebf.js" class="clover" data_turbolinks_track="true" debug="false"></script>
                        <script src="https://images.prasso.io/fbc/cdn.files/production/websites/designs/dusk/base-ae8948f5e23c447398a0e96992fcb396c6936d1bd5213c83a88a0134815f6158.js" debug="false" data-turbolinks-track="true" class="clover"></script>
                        <script>
                        //<![CDATA[
                        
                                var __REACT_ON_RAILS_EVENT_HANDLERS_RAN_ONCE__ = true
                        
                        //]]>
                        </script>
                        <script src="https://images.prasso.io/fbc/cdn.files/js/runtime-29643ceddd61d164b25a.js"></script>
                        <script src="https://images.prasso.io/fbc/cdn.files/js/1-794656562a19776f9d49.chunk.js"></script>
                        <script src="https://images.prasso.io/fbc/cdn.files/js/2-a38190a685725a895f06.chunk.js"></script>
                        <script src="https://images.prasso.io/fbc/cdn.files/js/media-1ab6fa2937934576a72f.chunk.js"></script>' 
                        ]);
                 }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
