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
        Schema::create('tblm_h_onboard_character_ref', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('link_regid')->nullable();
            $table->string('name', 100)->nullable();
            $table->string('prof_relation', 30)->nullable();
            $table->string('yearsknown', 20)->nullable();
            $table->string('company', 20)->nullable();
            $table->string('contact_mobile', 14)->nullable();
            $table->string('contact_email', 50)->nullable();
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
        Schema::dropIfExists('tblm_h_onboard_character_ref');
    }
};
