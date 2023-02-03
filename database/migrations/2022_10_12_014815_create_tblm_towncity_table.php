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
        Schema::create('tblm_towncity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_state_id')->constrained('tblm_state')->cascadeOnDelete();
            $table->integer('zip_code');
            $table->string('description', '50');
            $table->integer('createdby');
            $table->dateTime('datecreated');
            $table->integer('modifiedby')->nullable();
            $table->dateTime('datemodified')->nullable();

            $table->unique(['link_state_id', 'description'], 'state_description_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tblm_towncity');
    }
};
