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
        Schema::create('tblm_barangay', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_towncity_id')->constrained('tblm_towncity')->cascadeOnDelete();
            $table->string('description', '50');
            $table->integer('createdby');
            $table->dateTime('datecreated');
            $table->integer('modifiedby')->nullable();
            $table->dateTime('datemodified')->nullable();

            $table->unique(['link_towncity_id', 'description'], 'towncity_description_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tblm_barangay');
    }
};
