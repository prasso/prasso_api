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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id(); // Auto-incremented ID for system use only
            $table->uuid('guid')->unique(); // Unique GUID for identification
	        $table->string('name');
            $table->string('address');
            $table->string('city');
            $table->string('region');
            $table->string('country');
            $table->string('currency'); // Currency
            $table->decimal('tax_rates', 5, 2); // VAT
            $table->timestamps(); // Adds 'created_at' and 'updated_at' columns
            $table->foreignId('updated_by')->constrained('users');
        });

        Schema::create('vendor_ref_persons', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->unsignedBigInteger('vendor_id');
            $table->string('name');
            $table->string('address');
            $table->string('city');
            $table->string('region');
            $table->string('country');
            $table->unsignedBigInteger('contact_method_id');
            $table->timestamps();
            $table->foreign('vendor_id')->references('id')->on('vendors');
        });

        Schema::create('vendor_contact_methods', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->unsignedBigInteger('vendor_id'); //null or a vendor id
            $table->unsignedBigInteger('vendor_ref_person_id'); // null or a vendor ref person id
            $table->string('contact_type'); // Values: 'call', 'email', 'text', 'website'
            $table->string('contact_details'); // Phone number, email, URL, etc.
            $table->timestamps();
            
            // Choose the appropriate foreign key relationship based on 'contact_for'
            $table->foreign('vendor_id')->references('id')->on('vendors');
            $table->foreign('vendor_ref_person_id')->references('id')->on('vendor_ref_persons');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('vendor_ref_persons');
        Schema::dropIfExists('vendor_contact_methods');
    }
};
