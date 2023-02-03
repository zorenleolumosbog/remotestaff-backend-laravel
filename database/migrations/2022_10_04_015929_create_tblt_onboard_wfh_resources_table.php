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
        Schema::create('tblm_j_onboard_workfromhome_resource', function (Blueprint $table) {
            $table->increments('wfr_id');
            $table->integer('wfr_link_regid')->nullable();
            $table->string('wfr_workenv', 20)->nullable();
            $table->integer('wfr_nettype')->nullable();
            $table->integer('wfr_isp')->nullable();
            $table->string('wfr_internet_plan', 50)->nullable();
            $table->integer('wfr_plan_bandwidth')->nullable();
            $table->string('wfr_speedtest_url', 220)->nullable();
            $table->string('wfr_comp_hardwaretype', 14)->nullable();
            $table->string('wfr_comp_brandname', 14)->nullable();
            $table->string('wfr_comp_processor', 14)->nullable();
            $table->string('wfr_comp_os', 14)->nullable();
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
        Schema::dropIfExists('tblm_j_onboard_workfromhome_resource');
    }
};
