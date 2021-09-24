<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_name');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('NULL ON UPDATE CURRENT_TIMESTAMP'))->nullable();

        });

        DB::table('roles')->insert([   
            ['role_name' => 'admin', 'id' => 1],    
            ['role_name' => 'instructor', 'id' => 2] ,
            ['role_name' => 'appuser', 'id' => 3] 
         ]);
        Schema::create('user_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->foreignId('role_id')->index();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('NULL ON UPDATE CURRENT_TIMESTAMP'))->nullable();

        });

        //how roles work: Allow anyone with a login to log into the app. No role required
        //site-admins can log into the sites they have association with
        //super-admins can access any site admin area
       // INSERT INTO `yourdatabase`.`roles` (`role_name`) VALUES ('super-admin');
       // INSERT INTO `yourdatabase`.`roles` (`role_name`) VALUES ('site-admin');

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
