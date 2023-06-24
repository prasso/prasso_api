<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKeyFieldToSitePageTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_page_templates', function (Blueprint $table) {
            $table->string('template_data_model',100)->after('description')->nullable();
            $table->string('template_where_clause',100)->after('template_data_model')->nullable();
            $table->string('order_by_clause',500)->after('template_where_clause')->nullable();
            $table->boolean('include_csrf')->after('order_by_clause')->nullable()->default(false);
            $table->text('default_blank')->after('include_csrf')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
