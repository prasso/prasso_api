<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToSitePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_pages', function (Blueprint $table) {
            $table->tinyInteger('type')->default(1)->after('description')->comment('1=HTML, 2=S3 File, 3=URL');
            $table->string('external_url')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('site_pages', function (Blueprint $table) {
            $table->dropColumn(['type', 'external_url']);
        });
    }
}
