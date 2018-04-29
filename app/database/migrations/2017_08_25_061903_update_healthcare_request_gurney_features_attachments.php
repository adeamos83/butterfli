<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateHealthcareRequestGurneyFeaturesAttachments extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('healthcare_request', function (Blueprint $table) {
            $table->integer('respirator');
            $table->string('any_tubing');
            $table->string('colostomy_bag');
            $table->string('any_attachments')->nullable();
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
            $table->drop('respirator');
            $table->drop('any_tubing');
            $table->drop('colostomy_bag');
            $table->drop('any_attachments');

        });
    }

}
