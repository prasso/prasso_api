<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_pages', function (Blueprint $table) {
            // Only add type column if it doesn't exist
            if (!Schema::hasColumn('site_pages', 'type')) {
                $table->tinyInteger('type')->default(1)->after('description')->comment('1=HTML, 2=S3 File, 3=URL');
            }

            // Only add external_url column if it doesn't exist
            if (!Schema::hasColumn('site_pages', 'external_url')) {
                $table->string('external_url')->nullable()->after('type');
            }
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
            // Only drop columns if they exist
            if (Schema::hasColumn('site_pages', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('site_pages', 'external_url')) {
                $table->dropColumn('external_url');
            }
        });
    }
};