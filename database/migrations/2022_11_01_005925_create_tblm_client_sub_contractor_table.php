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
        Schema::connection('mysql')->create('tblm_client_sub_contractor', function (Blueprint $table) {
            $table->id();
            $table->integer('reg_link_preregid');
            $table->integer('actreg_contractor_id');
            $table->integer('subcon_legacy_id');
            $table->boolean('is_contracted');
            $table->date('date_contracted');
            $table->boolean('is_contract_end');
            $table->date('date_contract_end');
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
        Schema::dropIfExists('tblm_client_sub_contractor');
    }
};
