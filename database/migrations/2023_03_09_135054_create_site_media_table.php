<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_site_id');
            $table->string('s3media_url', 5000);
            $table->string('thumb_url', 500)->default('');
            $table->string('video_duration', 10)->nullable();
            $table->text('dimensions')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hls_media');
    }
}
