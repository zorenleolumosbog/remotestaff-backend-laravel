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
        Schema::connection('mysql2')->create('tblm_staff_invoice_hdr', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('link_client_subcon_pers_id');
            $table->foreign('link_client_subcon_pers_id')->references('id')->on('tblm_client_subcon_pers');
            $table->integer('link_staff_invoice_status_id');
            $table->datetime('invoice_date');
            $table->double('rate');
            $table->date('invoice_period_from');
            $table->date('invoice_period_to');
            $table->integer('createdby');
            $table->datetime('datecreated');
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
        Schema::connection('mysql2')->dropIfExists('tblm_staff_invoice_hdr');
    }
};
