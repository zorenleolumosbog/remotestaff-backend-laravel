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
        Schema::create('tbls_sub_pillar', function (Blueprint $table) {
            $table->id();
            $table->integer('link_pillar_id')->constrained('tbls_pillar')->cascadeOnDelete();
            $table->string('description', 50);
            $table->integer('createdby');
            $table->datetime('datecreated');
            $table->integer('modifiedby')->nullable();
            $table->dateTime('datemodified')->nullable();
            $table->integer('isactive')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbls_sub_pillar');
    }
};
