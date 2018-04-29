<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventFlagEventIdToPromoCodes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('promo_codes', function(Blueprint $table) {
            $table->tinyInteger('is_event')->default(0);
            $table->integer('event_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('promo_codes', function(Blueprint $table) {
            $table->dropColumn('is_event');
            $table->dropColumn('event_id');
        });
    }

}
