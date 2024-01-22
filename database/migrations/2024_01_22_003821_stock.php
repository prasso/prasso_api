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

        Schema::create('stock_categories', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('stock_categories');
            $table->string('name');
            $table->timestamps();
            $table->foreignId('updated_by')->nullable()->constrained('users');
        });
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->foreignId('category_id')->constrained('stock_categories');
            $table->string('item_description');
            $table->integer('quantity_on_hand');
            $table->integer('reorder_amount');
            $table->timestamps();
            $table->foreignId('updated_by')->nullable()->constrained('users');
        });

        Schema::create('stock_intents', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->foreignId('stock_id')->constrained('stocks');
            $table->string('work_order_number');
            $table->integer('quantity_needed');
            $table->timestamps();
            $table->foreignId('updated_by')->nullable()->constrained('users');
        });
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stocks');
            $table->integer('quantity_adjusted');
            $table->timestamps();
            $table->foreignId('updated_by')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('stock_intents');
        Schema::dropIfExists('stock_transactions');
        Schema::dropIfExists('stock_categories');
    }
};


 