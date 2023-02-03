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
        Schema::connection('mysql2')->create('tblm_client_basic_rate', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('link_client_id')->nullable();
            $table->integer('salary_type')->nullable();
            $table->decimal('basic_monthly_rate', 12,2)->nullable();
            $table->decimal('basic_weekly_rate', 12,2)->nullable();
            $table->decimal('basic_daily_rate', 12,2)->nullable();
            $table->decimal('basic_hourly_rate', 12,2)->nullable();
            $table->datetime('effective_date_from')->nullable();
            $table->datetime('effective_date_to')->nullable();
            $table->boolean('is_active')->nullable();
            $table->integer('createdby')->nullable();
            $table->datetime('datecreated')->nullable();
            $table->integer('modifiedby')->nullable();
            $table->datetime('datemodified')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->dropIfExists('tblm_client_basic_rate');
    }
};
