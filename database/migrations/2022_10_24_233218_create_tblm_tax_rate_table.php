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
        Schema::create('tblm_tax_rate', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_country_id')->nullable()->constrained('tblm_country')->cascadeOnDelete();
            $table->foreignId('link_state_id')->nullable()->constrained('tblm_state')->cascadeOnDelete();
            $table->foreignId('link_tax_type_id')->constrained('tblm_tax_type')->cascadeOnDelete();
            $table->boolean('state_applied');
            $table->string('description', '50')->nullable();
            $table->double('rate');
            $table->integer('createdby');
            $table->dateTime('datecreated');
            $table->integer('modifiedby')->nullable();
            $table->dateTime('datemodified')->nullable();

            $table->unique(['link_country_id', 'link_tax_type_id'], 'country_tax_type_unique');
            $table->unique(['link_state_id', 'link_tax_type_id'], 'state_tax_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tblm_tax_rate');
    }
};
