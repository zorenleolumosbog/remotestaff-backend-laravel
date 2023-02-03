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
        Schema::create('tblm_b_onboard_actreg_basic', function (Blueprint $table) {
            $table->increments('reg_id');
            $table->string('reg_prefix', 5)->nullable();
            $table->string('reg_nickname', 30)->nullable();
			$table->integer('registrant_type')->nullable();
			$table->string('reg_firstname', 50)->nullable();
			$table->string('reg_middlename', 50)->nullable();
			$table->string('reg_lastname', 50)->nullable();
            $table->date('reg_birthdate')->nullable();
            $table->integer('reg_civilstatus')->nullable();
            $table->string('reg_religion', 50)->nullable();
            $table->integer('reg_nationality')->nullable();
            $table->string('reg_gender', 50)->nullable();
            $table->string('reg_sss_id', 20)->nullable();
            $table->string('reg_philhealthid', 20)->nullable();
            $table->string('reg_pagibigid', 20)->nullable();
            $table->string('reg_tin', 20)->nullable();
            $table->string('reg_home_addr_line1', 50)->nullable();
			$table->string('reg_home_addr_line2', 50)->nullable();
			$table->integer('reg_home_addr_towncity')->nullable();
			$table->string('reg_prov_addr_line1', 50)->nullable();
            $table->string('reg_prov_addr_line2', 50)->nullable();	
			$table->integer('reg_prov_addr_towncity')->nullable();
            $table->string('reg_source', 20)->nullable();	
			$table->datetime('reg_datecreated')->nullable();
			$table->datetime('reg_datemodified')->nullable();
			$table->integer('reg_modifiedby')->nullable();
			$table->integer('reg_link_preregid')->nullable();
            $table->boolean('isSubcon')->nullable();
            $table->date('contract_effective_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tblm_b_onboard_actreg_basic');
    }
};
