<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateHealthcareProviderAddNewColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
    public function up()
    {
        Schema::table('healthcare_provider', function(Blueprint $table)
        {
            $table->string('operator_email')->nullable();
            $table->string('operator_phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('healthcare_provider', function(Blueprint $table)
        {
            $table->drop('operator_email');
            $table->drop('operator_phone');
        });
    }

}
