<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('produccion_det', function (Blueprint $table) {
            $table->json('pedidos_ids')->nullable()->after('id_pedidos_det');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('produccion_det', function (Blueprint $table) {
            $table->dropColumn('pedidos_ids');
        });
    }
};
