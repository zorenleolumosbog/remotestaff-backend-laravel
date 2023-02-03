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
        Schema::create('tblm_i_onboard_educ_attain', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('link_regid')->nullable();
            $table->string('degree_level', 20)->nullable();
            $table->string('major', 20)->nullable();
            $table->string('field', 20)->nullable();
            $table->string('institute', 20)->nullable();
            $table->string('country_id', 50)->nullable();
            $table->dateTime('graddate')->nullable();
            $table->string('gpa', 10)->nullable();
            $table->string('licensecert', 100)->nullable();
            $table->string('semtrainings', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tblm_i_onboard_educ_attain');
    }
};
