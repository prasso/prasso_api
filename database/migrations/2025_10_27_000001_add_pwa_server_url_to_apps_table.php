<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('apps', function (Blueprint $table) {
            $table->string('pwa_server_url', 2048)->nullable()->after('pwa_app_url')
                ->comment('Internal URL to the Node.js server for the PWA (e.g., http://localhost:3001)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apps', function (Blueprint $table) {
            $table->dropColumn('pwa_server_url');
        });
    }
};
