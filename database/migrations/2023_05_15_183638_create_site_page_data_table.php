<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSitePageDataTable extends Migration
{
    /**
     * Run the migrations.
     *siteid, data's key, json_data
     * @return void
     */
    public function up()
    {
        Schema::create('site_page_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_site_page_id');
            $table->string('data_key',500);
            $table->text('json_data')->nullable();
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
    }
}
