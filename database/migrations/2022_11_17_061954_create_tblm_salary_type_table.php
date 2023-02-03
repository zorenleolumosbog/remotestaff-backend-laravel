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
        Schema::connection('mysql2')->create('tblm_salary_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('desc', 20);
            $table->integer('createdby');
            $table->datetime('datecreated');
            $table->integer('modifiedby');
            $table->datetime('datemodified');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tblm_salary_type');
    }
};
