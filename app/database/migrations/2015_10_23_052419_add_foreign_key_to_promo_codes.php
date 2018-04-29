<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToPromoCodes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('promo_codes', function(Blueprint $table) {
            DB::statement('alter table promo_codes modify event_id int unsigned not null');
            //$table->foreign('event_id')->references('id')->on('user_events')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('promo_codes', function(Blueprint $table) {
            //$table->dropForeign('promo_codes_event_id_foreign');
        });
    }

}
