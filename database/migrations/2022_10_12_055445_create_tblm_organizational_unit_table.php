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
        Schema::create('tblm_organizational_unit', function (Blueprint $table) {
            $table->id();
            $table->string('ou_name', '50')->unique();
            $table->string('registered_legal_name', '50')->unique();
            $table->string('address_line1', '50');
            $table->string('address_line2', '50')->nullable();
            $table->integer('town_city')->constrained('tblm_towncity')->nullable();
            $table->boolean('is_hq')->nullable();
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
        Schema::dropIfExists('tblm_organizational_unit');
    }
};
