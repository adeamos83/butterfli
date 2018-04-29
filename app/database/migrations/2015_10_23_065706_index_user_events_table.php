<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IndexUserEventsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('user_events', function($table) {
            $table->index('id');
            $table->index('owner_id');
            $table->index('start_time');
            $table->index('server_start_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('user_events', function($table) {
            $table->dropIndex('user_events_id_index');
            $table->dropIndex('user_events_owner_id_index');
            $table->dropIndex('user_events_start_time_index');
            $table->dropIndex('user_events_server_start_time_index');
        });
    }

}
