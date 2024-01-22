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
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->decimal('amount_received', 10, 2);
            $table->enum('mode_of_payment', ['cash', 'bank', 'PDC']);
            $table->string('PDC_number')->nullable();
            $table->date('PDC_date')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('updation', ['confirmed', 'pending', 'cancelled']);
            $table->timestamps();
            $table->foreignId('updated_by')->nullable()->constrained('users');
        });
        Schema::create('receipts_invoice_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->constrained('receipts');
            $table->foreignId('invoice_id')->constrained('sales_invoices');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('receipts_invoice_numbers');
    }
};
