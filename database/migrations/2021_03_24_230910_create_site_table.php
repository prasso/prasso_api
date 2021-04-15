<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_pages', function (Blueprint $table) {
            $table->integer('fk_site_id')
                    ->after('id')
                    ->nullable();
        });
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('host', 500); //a list of hosts. ['www.barimorphosis.com','barimorphosis.com']
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
        Schema::dropIfExists('site');
    }
}
