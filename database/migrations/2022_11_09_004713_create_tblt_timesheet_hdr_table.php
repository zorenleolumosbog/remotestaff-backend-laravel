<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('tblt_timesheet_hdr', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('link_subcon_id');
            $table->integer('link_client_id');
            $table->integer('status_id');
            $table->decimal('work_total_hours', 7,2);
            $table->integer('createdby');
            $table->datetime('datecreated');
            $table->integer('modifiedby');
            $table->datetime('datemodified');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->dropIfExists('tblt_timesheet_hdr');
    }
};
