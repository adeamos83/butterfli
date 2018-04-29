<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IndexEventMembersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('event_members', function($table) {
            $table->index('id');
            $table->index('event_id');
            $table->index('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('event_members', function($table) {
            $table->dropIndex('event_members_id_index');
            $table->dropIndex('event_members_event_id_index');
            $table->dropIndex('event_members_owner_id_index');
        });
    }

}
