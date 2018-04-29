<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewFiendsToRequest extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('request', function(Blueprint $table) {
            $table->string('time_zone')->default('UTC');
            $table->string('src_address')->default('Address Not Available');
            $table->string('dest_address')->default('Address Not Available');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('request', function(Blueprint $table) {
            $table->dropColumn('time_zone');
            $table->dropColumn('src_address');
            $table->dropColumn('dest_address');
        });
    }

}
