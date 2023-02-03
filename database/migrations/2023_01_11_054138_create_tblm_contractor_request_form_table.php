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
        Schema::create('tblm_contractor_request_form', function (Blueprint $table) {
            $table->id('id');
            $table->integer('link_prereg_id')->nullable();
            $table->integer('bh_jo_id')->nullable();
            $table->string('crf_title', 100)->nullable();
            $table->integer('crf_no_staffs')->nullable();
            $table->longText('crf_description')->nullable();
            $table->string('crf_timezone', 100)->nullable();
            $table->string('crf_ofshore', 5)->nullable();
            $table->string('crf_hourly_rate', 30)->nullable();
            $table->string('crf_expertise_level', 50)->nullable();
            $table->string('crf_adv_skills', 255)->nullable();
            $table->string('crf_mid_skills', 255)->nullable();
            $table->string('crf_expected_tof', 255)->nullable();
            $table->longText('crf_role_obj')->nullable();
            $table->longText('crf_industry')->nullable();
            $table->string('crf_os', 30)->nullable();
            $table->longText('crf_required_tools')->nullable();
            $table->string('crf_au_number', 30)->nullable();
            $table->string('crf_monitors', 5)->nullable();
            $table->string('crf_comm_tools', 20)->nullable();
            $table->string('crf_existing_team', 50)->nullable();
            $table->integer('crf_company_age')->nullable();
            $table->string('crf_no_employees', 20)->nullable();
            $table->string('crf_sourcing', 20)->nullable();
            $table->string('crf_team_size', 20)->nullable();
            $table->string('crf_job_type', 20)->nullable();
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
        Schema::dropIfExists('tblm_contractor_request_form');
    }
};
