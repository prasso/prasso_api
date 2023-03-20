<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleToSiteMedia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_media', function (Blueprint $table) {
            $table->timestamp('media_date')->after('fk_site_id')->nullable();
            $table->string('media_description', 1500)->after('fk_site_id')->nullable();
            $table->string('media_title', 500)->after('fk_site_id')->nullable();
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
