<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreateDateTimeToRequest extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('request', function(Blueprint $table) {
            $table->string('req_create_user_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('request', function(Blueprint $table) {
            $table->dropColumn('req_create_user_time');
        });
    }

}
