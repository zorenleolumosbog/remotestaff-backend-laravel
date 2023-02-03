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
        Schema::connection('mysql2')->create('tblt_invoice_dtl', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('link_inv_hdr')->unsigned();
            $table->foreign('link_inv_hdr')->references('id')->on('tblt_invoice_hdr');
            $table->longtext('particular');
            $table->decimal('hours_rendered', 12,2);
            $table->decimal('rate_per_hour', 12,2);
            $table->decimal('billable_amt', 12,2);
            $table->integer('status_id');
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
        Schema::connection('mysql2')->dropIfExists('tblt_invoice_dtl');
    }
};
