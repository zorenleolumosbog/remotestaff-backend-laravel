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
        Schema::connection('mysql2')->create('tblt_timesheet_dtl', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('link_tms_hdr')->unsigned();
            $table->foreign('link_tms_hdr')->references('id')->on('tblt_timesheet_hdr');
            $table->date('date_worked');
            $table->time('work_time_in');
            $table->time('work_time_out');
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
        Schema::connection('mysql2')->dropIfExists('tblt_timesheet_dtl');
    }
};
