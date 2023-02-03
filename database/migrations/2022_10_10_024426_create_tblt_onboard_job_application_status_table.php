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
        Schema::create('tblm_k_onboard_applied_jobs', function (Blueprint $table) {
            $table->id('ja_id');
            $table->integer('ja_link_preregid')->nullable();
            $table->dateTime('ja_application_date')->nullable();
            $table->string('ja_job_title', 100)->nullable();
            $table->string('ja_application_status', 20)->nullable();
            $table->string('ja_job_status', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tblm_k_onboard_applied_jobs');
    }
};
