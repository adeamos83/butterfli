<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProviderTransferDateToWalker extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('walker', function(Blueprint $table) {
            $table->dateTime('provider_transfer_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('walker', function(Blueprint $table) {
            $table->dropColumn('provider_transfer_date');
        });
    }

}
