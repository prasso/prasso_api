<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // table holding pending notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_sender')->nullable()->index();
            $table->foreignId('user_receiver')->nullable()->index();
            $table->string('subject', 200);
            $table->string('body', 500);
            $table->string('action', 100);
            $table->timestamp('schedule_date_time')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('NULL ON UPDATE CURRENT_TIMESTAMP'))->nullable();
        });

        // table holding sent notifications
        Schema::create('notifications_sent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_sender')->nullable()->index();
            $table->foreignId('user_receiver')->nullable()->index();
            $table->string('subject', 200);
            $table->string('body', 500);
            $table->string('action', 100);
            $table->timestamp('schedule_date_time')->useCurrent();
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
        Schema::dropIfExists('notifications');
    }
}
