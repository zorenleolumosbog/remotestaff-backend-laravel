<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('tblm_client', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('client_id_legacy')->nullable();
            $table->string('client_name', 50)->nullable();
            $table->string('client_poc', 50)->nullable();
            $table->string('client_poc_position', 50)->nullable();
            $table->string('client_addr_line1', 50)->nullable();
            $table->string('client_addr_line2', 50)->nullable();
            $table->integer('client_towncity')->nullable();
            $table->string('client_email', 50)->nullable();
            $table->string('client_ABN', 50)->nullable();
            $table->string('client_phone', 50)->nullable();
            $table->integer('client_currency')->nullable()->default('2');;
            $table->integer('createdby')->nullable();
            $table->datetime('datecreated')->nullable();
            $table->integer('modifiedby')->nullable();
            $table->datetime('datemodified')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->dropIfExists('tblm_client');
    }
};
