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
        Schema::create('tblm_d_onboard_contact_social_media', function (Blueprint $table) {
            $table->id();
            $table->integer('link_regid')->nullable();
            $table->integer('link_social_media')->nullable();
            $table->string('social_media_url', '50')->nullable();
            $table->integer('createdby')->nullable();
            $table->dateTime('datecreated')->nullable();
            $table->integer('modifiedby')->nullable();
            $table->dateTime('datemodified')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tblm_d_onboard_contact_social_media');
    }
};
