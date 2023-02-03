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
        Schema::create('tblo_dept_sec_pers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_sec_id')->constrained('tblo_dept_sec')->cascadeOnDelete();;
            $table->integer('link_prereg_id');
            $table->integer('createdby');
            $table->datetime('datecreated');
            $table->integer('modifiedby')->nullable();
            $table->dateTime('datemodified')->nullable();

            $table->unique(['link_sec_id', 'link_prereg_id'], 'sec_prereg_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tblo_dept_sec_pers');
    }
};
