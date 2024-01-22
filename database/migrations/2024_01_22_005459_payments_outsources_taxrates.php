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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->decimal('amount', 10, 2);
            $table->enum('mode_of_payment', ['cash', 'bank', 'PDC']);
            $table->string('PDC_number')->nullable();
            $table->date('PDC_date')->nullable();
            $table->enum('updation', ['confirmed', 'cancelled']);
            $table->timestamps();
            $table->foreignId('updated_by')->nullable()->constrained('users');
        });
        Schema::create('payments_invoice_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments');
            $table->foreignId('invoice_id')->constrained('sales_invoices');
            $table->timestamps();
        });
        Schema::create('outsources', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->string('work_order');
            $table->text('job_description');
            $table->integer('quantity');
            $table->date('delivery_date');
            $table->decimal('rate', 8, 2);
            $table->integer('returned_quantity')->default(0);
            $table->integer('damage')->default(0);
            $table->timestamps();
            $table->foreignId('updated_by')->nullable()->constrained('users');
        });
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('region')->unique();
            $table->decimal('rate', 10, 2);
            $table->timestamps();
            $table->foreignId('updated_by')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payments_invoice_numbers');
        Schema::dropIfExists('outsources');
        Schema::dropIfExists('tax_rates');
    }
};


