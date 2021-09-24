<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BuildFaqsStorage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //faqs_storage
        Schema::create('faqs_storage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('active')->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('label', 50)->nullable()->index();
            $table->string('title', 500)->nullable();
            $table->text('details')->nullable();
            $table->integer('sort_order')->index();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('NULL ON UPDATE CURRENT_TIMESTAMP'))->nullable();
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
