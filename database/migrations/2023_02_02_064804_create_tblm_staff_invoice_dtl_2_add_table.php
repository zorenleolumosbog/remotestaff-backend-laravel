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
        Schema::connection('mysql2')->create('tblm_staff_invoice_dtl_2_add', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_staff_invoice_hdr_id')->constrained('tblm_staff_invoice_hdr')->cascadeOnDelete();
            $table->string('description');
            $table->double('quantity');
            $table->double('unit_price');
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
        Schema::connection('mysql2')->dropIfExists('tblm_staff_invoice_dtl_2_add');
    }
};
