<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_page_templates', function (Blueprint $table) {
            $table->id();
            $table->string('templatename',100);
            $table->string('title',500);
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        DB::table('site_page_templates')->insert(
            array(
                'templatename' => 'sitepage.templates.past_livestreams',
                'title' => 'Completed Livestreams Page',
                'description' => 'A page used by sites that use livestream functionality. This is the default page for presenting completed livestreamed events.',
                'created_at' => now(),
                'updated_at' => now()
            )
        );
        DB::table('site_page_templates')->insert(
            array(
                'templatename' => 'sitepage.templates.welcome',
                'title' => 'Welcome Page with Registration',
                'description' => 'This is a landing page for the site that has memberships.  It is used to welcome visitors and allow them to register for the site.',
                'created_at' => now(),
                'updated_at' => now()
            )
        );

        DB::table('site_page_templates')->insert(
            array(
                'templatename' => 'sitepage.templates.welcome_no_registration',
                'title' => 'Welcome Page with Registration',
                'description' => 'This is a landing page for the site that does not have memberships.',
                'created_at' => now(),
                'updated_at' => now()
            )
        );
        DB::table('site_page_templates')->insert(
            array(
                'templatename' => 'sitepage.templates.dashboard',
                'title' => 'Dashboard Page',
                'description' => 'A page used by sites that support registration. This is a landing page for those who are logged in.',
                'created_at' => now(),
                'updated_at' => now()
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('template');
    }
}
