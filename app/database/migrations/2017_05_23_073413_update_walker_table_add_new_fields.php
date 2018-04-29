<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateWalkerTableAddNewFields extends Migration {

	/**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('walker', function($table) {
//            $table->string('company',255)->nullable();
//            $table->string('license_number',30)->nullable();
//            $table->string('license_state',30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('walker', function($table) {
//            $table->dropColumn('company',255)->nullable();
//            $table->dropColumn('license_number',30)->nullable();
//            $table->dropColumn('license_state',30)->nullable();
        });
    }

}
