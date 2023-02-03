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
        Schema::create('tblm_a_onboard_prereg', function (Blueprint $table) {
            $table->id();
			$table->integer('link_social_media_id')->nullable();
			$table->string('email', 50)->nullable();
			$table->dateTime('email_verified_at')->nullable();
			$table->string('password', 150)->collation('utf8mb4_unicode_ci')->nullable();
			$table->string('email_passwd_conf', 150)->collation('utf8mb4_unicode_ci')->nullable();
			$table->ipAddress('ip_addr', 16)->nullable();
			$table->dateTime('date_submitted')->nullable();
			$table->boolean('is_verified')->nullable();
			$table->boolean('is_social_media')->nullable();
			$table->dateTime('date_verified')->nullable();
			$table->integer('maxdays_rule_id')->nullable();
			$table->integer('maxdays_unverifed')->nullable();
			$table->boolean('is_expired')->nullable();
			$table->dateTime('date_expired')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tblm_a_onboard_prereg');
    }
};
