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
        Schema::create('tblm_f_onboard_work_preference', function (Blueprint $table) {
            $table->increments('wp_id');
            $table->integer('wp_link_regid')->nullable();
            $table->integer('wp_availability')->nullable();
            $table->integer('wp_emp_preference')->nullable();
            $table->integer('wp_timezone')->nullable();
            $table->string('wp_latest_job_title', 50)->nullable();
            $table->integer('wp_workingmodel')->nullable();
            $table->decimal('wp_fulltime_agreedsalary',9,2)->nullable();
            $table->decimal('wp_parttime_agreedsalary',9,2)->nullable();
            $table->integer('wp_years_of_exp')->nullable();
            $table->integer('wp_createdby')->nullable();
            $table->datetime('wp_datecreated')->nullable();
            $table->datetime('wp_datemodified')->nullable();
            $table->integer('wp_modifiedby')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tblm_f_onboard_work_preference');
    }
};
