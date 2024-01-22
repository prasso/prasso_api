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
            Schema::create('sales_invoices', function (Blueprint $table) {
                $table->id(); // Auto-incremented ID for system use only
                $table->uuid('guid')->unique(); // Unique GUID for identification
                $table->unsignedBigInteger('quotation_id'); // Foreign key to quotations table
                $table->enum('payment_method', ['cash', 'credit']); // Cash or credit
                $table->integer('credit_period')->nullable(); // Credit period (nullable for cash transactions)
                $table->decimal('amount_due', 10, 2); // Amount due
                $table->decimal('discount', 5, 2)->nullable(); // Discount (nullable)
                $table->decimal('vat', 5, 2); // VAT
                $table->text('comments')->nullable(); // Comments (nullable)
                $table->unsignedBigInteger('sales_person_id'); // Foreign key from user table (sales person)
                $table->timestamps(); // Adds 'created_at' and 'updated_at' columns
                $table->foreignId('updated_by')->constrained('users');

                // Foreign key constraints
                $table->foreign('quotation_id')->references('id')->on('quotations');
                $table->foreign('sales_person_id')->references('id')->on('users');
            });

            Schema::create('sales_details', function (Blueprint $table) {
                $table->id(); // Auto-incremented ID for system use only
                $table->uuid('guid')->unique(); // Unique GUID for identification
                $table->unsignedBigInteger('sales_id'); // Foreign key to sales table
                $table->string('particulars'); // Details or particulars
                $table->integer('quantity'); // Quantity
                $table->decimal('rate', 8, 2); // Rate
                $table->decimal('amount', 10, 2); // Amount
                $table->text('comments')->nullable(); // Comments (nullable)
                $table->timestamps(); // Adds 'created_at' and 'updated_at' columns
                $table->foreignId('updated_by')->constrained('users');
                // Foreign key constraint
                $table->foreign('sales_id')->references('id')->on('sales_invoices');
            });
        }
    
        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('sales_invoices');
            Schema::dropIfExists('sales_details');
        }
};
