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
        Schema::connection('mysql2')->create('tblt_timesheet_adj_dtl', function (Blueprint $table) {
            $table->id();
            $table->integer('link_adj_hdr_id')->nullable();
            $table->integer('link_timesheet_dtl_id')->nullable();
            $table->date('date')->nullable();
            $table->decimal('adjusted_hours',7,2)->nullable();
            $table->integer('createdby')->nullable();
            $table->dateTime('datecreated')->nullable();
            $table->integer('modifiedby')->nullable();
            $table->dateTime('datemodified')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tblt_timesheet_adj_dtl');
    }
};
