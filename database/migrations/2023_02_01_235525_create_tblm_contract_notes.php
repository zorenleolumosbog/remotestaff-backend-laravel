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
        Schema::create('tblm_contract_notes', function (Blueprint $table) {
            $table->id();
            $table->string('notes');
            $table->integer('link_filetype_id')->constrained('tblm_filetype')->cascadeOnDelete()->nullable();
            $table->string('filename',50)->nullable();
            $table->string('path')->nullable();
            $table->integer('createdby');
            $table->datetime('datecreated');
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
        Schema::dropIfExists('tblm_contract_notes');
    }
};
