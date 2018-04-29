<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IndexUserPromoUsedTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('user_promo_used', function($table) {
            $table->index('code_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('user_promo_used', function($table) {
            $table->dropIndex('user_promo_used_code_id_index');
            $table->dropIndex('user_promo_used_user_id_index');
        });
    }

}
