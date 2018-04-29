<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateHealthcareRequest extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('healthcare_request', function (Blueprint $table) {
            $table->string('agent_first_name')->nullable();
            $table->string('agent_last_name')->nullable();
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
            $table->drop('agent_first_name');
            $table->drop('agent_last_name');

        });
    }
}
