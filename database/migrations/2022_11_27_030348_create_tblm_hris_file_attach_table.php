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
        Schema::create('tblm_hris_file_attach', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('filetype_201')->nullable();
            $table->string('filename', 150)->nullable();
            $table->string('path', 150)->nullable();
            $table->string('filetype', 50)->nullable();
            $table->dateTime('dateuploaded')->nullable();
            $table->integer('uploadby')->nullable();
            $table->integer('createdby');
            $table->dateTime('datecreated');
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
        Schema::dropIfExists('tblm_hris_file_attach');
    }
};
