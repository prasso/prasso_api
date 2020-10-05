<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_id')->nullable()->index();
            $table->string('icon', 100)->nullable();
            $table->string('label', 50)->nullable();
            $table->string('page_title', 500)->nullable();
            $table->string('page_url', 500);
            $table->integer('sort_order')->index();
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
        Schema::dropIfExists('tabs');
    }
}
