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
        Schema::create('tblm_e_onboard_work_history', function (Blueprint $table) {
            $table->increments('we_id');
            $table->integer('we_link_reg_id')->nullable();
            $table->string('we_position_held', 40)->nullable();
            $table->string('we_er_name', 40)->nullable();
            $table->date('we_start_date')->nullable();
            $table->date('we_end_date')->nullable();
            $table->integer('we_country_id')->nullable();
            $table->integer('we_createdby')->nullable();
            $table->dateTime('we_datecreated')->nullable();
            $table->integer('we_modifiedby')->nullable();
            $table->dateTime('we_datemodified')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tblm_e_onboard_work_history');
    }
};
