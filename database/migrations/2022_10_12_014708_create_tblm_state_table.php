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
        Schema::create('tblm_state', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_country_id')->nullable()->constrained('tblm_country')->cascadeOnDelete();
            $table->foreignId('link_region_id')->nullable()->constrained('tblm_region')->cascadeOnDelete();
            $table->string('description', '50');
            $table->integer('createdby');
            $table->dateTime('datecreated');
            $table->integer('modifiedby')->nullable();
            $table->dateTime('datemodified')->nullable();

            $table->unique(['link_country_id', 'description'], 'country_description_unique');
            $table->unique(['link_region_id', 'description'], 'state_description_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tblm_state');
    }
};
