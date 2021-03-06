<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateWalkerTableAddIsAuthorize extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
    public function up()
    {
        Schema::table('walker', function (Blueprint $table) {
            $table->integer('is_authorize')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('walker', function (Blueprint $table) {
            $table->dropColumn('is_authorize');
        });
    }

}
