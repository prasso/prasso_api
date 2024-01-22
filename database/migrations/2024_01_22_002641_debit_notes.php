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
        Schema::create('debit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->foreignId('purchase_invoice_id')->constrained('purchase_invoices');
            $table->decimal('amount_of_debitnote', 10, 2);
            $table->decimal('vat', 8, 2);
            $table->decimal('discount', 8, 2);
            $table->timestamps();
            $table->foreignId('updated_by')->constrained('users');
        });

        Schema::create('debit_note_details', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->foreignId('debitnote_id')->constrained('debit_notes');
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
        Schema::dropIfExists('debit_notes');
        Schema::dropIfExists('debit_note_details');
    }
};
