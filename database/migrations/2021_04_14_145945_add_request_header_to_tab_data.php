<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequestHeaderToTabData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tabs', function (Blueprint $table) {
            $table->string('request_header', 500)
            ->after('sort_order')
            ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tab_data', function (Blueprint $table) {
            //
        });
    }
}
