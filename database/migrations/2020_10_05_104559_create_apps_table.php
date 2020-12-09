<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('token_id')->nullable()->index();
            $table->foreignId('team_id')->nullable()->index();
            $table->string('appicon', 100)->nullable();
            $table->string('app_name', 50)->nullable();
            $table->string('page_title', 500)->nullable();
            $table->string('page_url', 500);
            $table->integer('sort_order');
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
        Schema::dropIfExists('apps');
    }
}
