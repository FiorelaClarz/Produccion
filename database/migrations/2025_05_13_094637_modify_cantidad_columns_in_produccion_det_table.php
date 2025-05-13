<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyCantidadColumnsInProduccionDetTable extends Migration
{
    public function up()
    {
        Schema::table('produccion_det', function (Blueprint $table) {
            $table->decimal('cantidad_pedido', 10, 2)->change();
            $table->decimal('cantidad_producida_real', 10, 2)->change();
        });
    }

    public function down()
    {
        Schema::table('produccion_det', function (Blueprint $table) {
           
            $table->integer('cantidad_pedido')->change();
            $table->integer('cantidad_producida_real')->change();
        });
    }
}
