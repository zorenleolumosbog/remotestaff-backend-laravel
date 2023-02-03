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
        Schema::create('tblm_c_onboard_actreg_file_attach', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('link_regid')->nullable();
            $table->string('filename', 150)->nullable();
            $table->string('path', 150)->nullable();
            $table->integer('jobseeker_filetype')->nullable();
            $table->integer('filetype')->nullable();
            $table->string('fileext', 10)->nulllable();
            $table->dateTime('dateuploaded')->nullable();
            $table->integer('uploadby')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tblm_c_onboard_actreg_file_attach');
    }
};
