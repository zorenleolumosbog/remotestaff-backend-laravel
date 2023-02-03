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
        Schema::create('tblo_dept_sec', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_dept_id')->constrained('tblo_dept')->cascadeOnDelete();
            $table->string('description', 50);
            $table->integer('createdby');
            $table->datetime('datecreated');
            $table->integer('modifiedby')->nullable();
            $table->dateTime('datemodified')->nullable();

            $table->unique(['link_dept_id', 'description'], 'dept_description_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tblo_dept_sec');
    }
};
