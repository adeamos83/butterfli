<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRideRequest extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request', function(Blueprint $table) {
            $table->integer('roundtrip_id')->nullable();
            $table->integer('hospital_provider_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request', function(Blueprint $table) {
            $table->dropColumn('roundtrip_id');
            $table->dropColumn('hospital_provider_id');
        });
    }

}

