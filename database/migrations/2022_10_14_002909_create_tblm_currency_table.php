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
        Schema::create('tblm_currency', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_country_id')->unique()->constrained('tblm_country')->cascadeOnDelete();
            $table->string('code', '5')->unique();
            $table->string('symbol', '5');
            $table->string('description', '40')->unique();
            $table->double('rate');
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
        Schema::dropIfExists('tblm_currency');
    }
};
