<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewTableDocuments extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
    public function up()
    {
        Schema::create('healthcare_documents', function(Blueprint $table) {
            $table->increments('id');
            $table->Integer('request_id')->nullable();
            $table->Integer('healthcare_id')->nullable();
            $table->Integer('agent_id')->nullable();
            $table->string('document_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('healthcare_documents');
    }


}
