<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToUserEvents extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('user_events', function(Blueprint $table) {
            DB::statement('alter table user_events modify owner_id int unsigned not null');
            $table->foreign('owner_id')->references('id')->on('owner')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('user_events', function(Blueprint $table) {
            $table->dropForeign('user_events_owner_id_foreign');
        });
    }

}
