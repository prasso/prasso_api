<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id(); // Auto-incremented ID for system use only
            $table->uuid('guid')->unique(); // Unique GUID for identification
            $table->unsignedBigInteger('sales_invoice_id'); // Foreign key to sales invoices table
            $table->decimal('amount_of_credit', 10, 2); // Amount of credit
            $table->decimal('vat', 5, 2); // VAT
            $table->decimal('discount', 5, 2)->nullable(); // Discount (nullable)
            $table->timestamps(); // Adds 'created_at' and 'updated_at' columns
            $table->foreignId('updated_by')->constrained('users');

            // Foreign key constraint
            $table->foreign('sales_invoice_id')->references('id')->on('sales_invoices');
        });

        Schema::create('credit_details', function (Blueprint $table) {
            $table->id(); // Auto-incremented ID for system use only
            $table->uuid('guid')->unique(); // Unique GUID for identification
            $table->unsignedBigInteger('credit_note_id'); // Foreign key to credit note table
            $table->unsignedBigInteger('sales_invoice_detail_id'); // Foreign key to sales invoice details table
            $table->integer('quantity'); // Quantity
            $table->decimal('rate', 8, 2); // Rate
            $table->decimal('amount', 10, 2); // Amount
            $table->timestamps(); // Adds 'created_at' and 'updated_at' columns

            // Foreign key constraints
            $table->foreign('credit_note_id')->references('id')->on('credit_notes');
            $table->foreign('sales_invoice_detail_id')->references('id')->on('sales_details');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('credit_details');
    }
};
