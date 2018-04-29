<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexingToPromoCodes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('promo_codes', function($table) {
            $table->index('is_event');
            $table->index('event_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('promo_codes', function($table) {
            $table->dropIndex('promo_codes_is_event_index');
            $table->dropIndex('promo_codes_event_id_index');
        });
    }

}
