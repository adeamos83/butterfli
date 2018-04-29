<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRetryFlagToScheduledRequests extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('scheduled_requests', function(Blueprint $table) {
            $table->integer('retryflag')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('scheduled_requests', function(Blueprint $table) {
            $table->dropColumn('retryflag');
        });
    }

}
