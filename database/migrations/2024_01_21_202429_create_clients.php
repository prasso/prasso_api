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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->string('name');
            $table->string('address');
            $table->string('city');
            $table->string('region');
            $table->string('country');
            // will create a separate table for client contact methods and establish a relationship
            $table->string('currency');
            $table->string('tax_rates');
            $table->string('default_comments');
            $table->string('default_invoice_number');
            $table->string('default_quotation_number');
            $table->string('period_of_entry');
            $table->string('printing_settings_quotation');
            $table->string('invoice_reports');
            $table->boolean('deletion_of_quotation');
            $table->boolean('invoice_in_full');
            $table->timestamps();
            $table->timestamp('date_added')->useCurrent();
            $table->timestamp('date_updated')->useCurrent();
            $table->foreignId('updated_by')->constrained('users');
        });

        Schema::create('client_sales_persons', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->unsignedBigInteger('client_id');
            $table->string('name');
            $table->string('address');
            $table->string('city');
            $table->string('region');
            $table->string('country');
            $table->unsignedBigInteger('contact_method_id');
            $table->timestamps();
            $table->foreign('client_id')->references('id')->on('clients');
        });

        Schema::create('client_contact_methods', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->unsignedBigInteger('client_id'); //null or a client id
            $table->unsignedBigInteger('client_sales_person_id'); // null or a client sales person id
            $table->string('contact_type'); // Values: 'call', 'email', 'text', 'website'
            $table->string('contact_details'); // Phone number, email, URL, etc.
            $table->timestamps();
            
            // Choose the appropriate foreign key relationship based on 'contact_for'
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('client_sales_person_id')->references('id')->on('client_sales_persons');
            
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
        Schema::dropIfExists('client_sales_persons');
        Schema::dropIfExists('client_contact_methods');
   
    }
};
