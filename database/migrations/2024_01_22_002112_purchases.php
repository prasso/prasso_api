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
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->integer('credit_period');
            $table->decimal('amount_of_purchase', 10, 2);
            $table->decimal('vat', 8, 2);
            $table->decimal('discount', 8, 2);
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->foreignId('updated_by')->constrained('users');
        });

        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->foreignId('purchase_id')->constrained('purchase_invoices');
            $table->string('particulars');
            $table->integer('quantity');
            $table->decimal('rate', 8, 2);
            $table->decimal('amount', 10, 2);
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->foreignId('updated_by')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_invoices');
        Schema::dropIfExists('purchase_details');
    }
};
