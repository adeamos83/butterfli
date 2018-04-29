<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddServerStartTimeToScheduledRequests extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('scheduled_requests', function(Blueprint $table) {
            $table->dateTime('server_start_time');
            $table->dropColumn('start_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('scheduled_requests', function(Blueprint $table) {
            $table->dropColumn('server_start_time');
        });
    }

}
