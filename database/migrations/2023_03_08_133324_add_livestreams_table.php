<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLivestreamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('livestream_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_site_id');
            $table->string('queue_folder', 500); //where livestream files are stored from IVS
            $table->string('presentation_folder', 500); //where livestream files are stored for presentation
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
        //
    }
}
