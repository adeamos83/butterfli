<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyScheduledRequests extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('scheduled_requests', function(Blueprint $table) {
            DB::statement('alter table scheduled_requests modify owner_id int unsigned not null');
            $table->foreign('owner_id')->references('id')->on('owner')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('scheduled_requests', function(Blueprint $table) {
            $table->dropForeign('scheduled_requests_owner_id_foreign');
        });
    }

}
