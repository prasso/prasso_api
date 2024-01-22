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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->unsignedBigInteger('client_id');
            $table->decimal('total_amount', 10, 2); // Adjust the precision and scale based on your needs
            $table->enum('quotation_status', ['approved', 'rejected', 'cancel']);
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->foreignId('updated_by')->constrained('users');
            
            $table->foreign('client_id')->references('id')->on('clients');
        });

        Schema::create('quotation_details', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->unsignedBigInteger('quotation_id');
            $table->text('particulars');
            $table->integer('quantity');
            $table->decimal('rate', 10, 2); // Adjust the precision and scale based on your needs
            $table->decimal('amount', 10, 2); // Adjust the precision and scale based on your needs
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->foreignId('updated_by')->constrained('users');
            
            $table->foreign('quotation_id')->references('id')->on('quotations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('quotation_details');
    }
};
