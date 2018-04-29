<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateHealthcareRequestGurneyFeatures extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
{
    Schema::table('healthcare_request', function (Blueprint $table) {
        $table->integer('oxygen_mask');
        $table->string('height')->nullable();
        $table->string('weight')->nullable();
        $table->string('condition')->nullable();
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
            $table->drop('oxygen_mask');
            $table->drop('height');
            $table->drop('weight');
            $table->drop('condition');

        });
    }

}
