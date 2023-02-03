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
        Schema::connection('mysql2')->create('tblt_timesheet_adj_hdr', function (Blueprint $table) {
            $table->id();
            $table->dateTime('tran_date')->nullable();
            $table->integer('client_id')->nullable();
            $table->integer('subcon_id')->nullable();
            $table->boolean('isvalid')->nullable();
            $table->boolean('isposted')->nullable();
            $table->boolean('isvoid')->nullable();
            $table->string('void_reason', 30);
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
        Schema::dropIfExists('tblt_timesheet_adj_hdr');
    }
};
