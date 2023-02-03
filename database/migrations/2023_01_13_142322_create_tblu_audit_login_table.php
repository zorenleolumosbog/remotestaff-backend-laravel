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
        Schema::create('tblu_audit_login', function (Blueprint $table) {
            $table->id();
            $table->integer('link_prereg_id')->nullable();
            $table->dateTime('logged_in')->nullable();
            $table->dateTime('logged_out')->nullable();
            $table->string('ip_addr', 15)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tblu_audit_login');
    }
};
