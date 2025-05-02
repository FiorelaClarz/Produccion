<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHoraLimiteToPedidosCabTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pedidos_cab', function (Blueprint $table) {
            $table->time('hora_limite')->nullable()->after('id_hora_limite');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pedidos_cab', function (Blueprint $table) {
            $table->dropColumn('hora_limite');
        });
    }
}