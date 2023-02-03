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
        Schema::connection('mysql2')->create('tblt_invoice_hdr', function (Blueprint $table) {
            $table->increments('id');
            $table->date('inv_date');
            $table->integer('link_client_id');
            $table->date('inv_period_from');
            $table->date('inv_period_to');
            $table->decimal('gross_amt', 12,2);
            $table->decimal('perc_discount', 12,2);
            $table->decimal('discount_amt', 12,2);
            $table->decimal('net_amt', 12,2);
            $table->integer('status_id');
            $table->integer('is_void');
            $table->string('void_reason', 30);
            $table->integer('voidedby');
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
        Schema::connection('mysql2')->dropIfExists('tblt_invoice_hdr');
    }
};
