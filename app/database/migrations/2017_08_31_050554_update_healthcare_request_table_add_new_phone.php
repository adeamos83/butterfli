<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateHealthcareRequestTableAddNewPhone extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
    public function up()
    {
        Schema::table('healthcare_request', function (Blueprint $table) {
            $table->string('agent_phone')->nullable();
            $table->integer('roundtrip_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('healthcare_request', function (Blueprint $table) {
            $table->dropColumn('agent_phone');
            $table->dropColumn('roundtrip_id');
        });
    }

}
