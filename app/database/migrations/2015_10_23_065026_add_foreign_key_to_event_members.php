<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToEventMembers extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('event_members', function(Blueprint $table) {
            DB::statement('alter table event_members modify event_id int unsigned not null');
            DB::statement('alter table event_members modify owner_id int unsigned not null');
            $table->foreign('event_id')->references('id')->on('user_events')->onDelete('cascade');
            $table->foreign('owner_id')->references('id')->on('owner')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('event_members', function(Blueprint $table) {
            $table->dropForeign('event_members_event_id_foreign');
            $table->dropForeign('event_members_owner_id_foreign');
        });
    }

}
