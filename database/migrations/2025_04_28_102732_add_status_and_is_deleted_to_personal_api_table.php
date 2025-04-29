<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusAndIsDeletedToPersonalApiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('personal_api', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('id_tiendas_api');
            $table->boolean('is_deleted')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personal_api', function (Blueprint $table) {
            $table->dropColumn(['status', 'is_deleted']);
        });
    }
}