<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateMasterpageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('masterpages', function (Blueprint $table) {
            $table->id();
            $table->string('pagename',100);
            $table->string('title',500);
            $table->text('description')->nullable();
            $table->timestamps();
        });
        //add the default records, sitepage.templates.masterpage and sitepage.templates.blank
        DB::table('masterpages')->insert(
            array(
                'pagename' => 'sitepage.templates.masterpage',
                'title' => 'Master Page',
                'description' => 'This is the default master page for the site.  It is used for all pages unless a specific master page is defined for a page.',
                'created_at' => now(),
                'updated_at' => now()
            )
        );
        DB::table('masterpages')->insert(
            array(
                'pagename' => 'sitepage.templates.blankpage',
                'title' => 'Blank Page',
                'description' => 'This is a blank page with no master page.',
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
        //
    }
}
