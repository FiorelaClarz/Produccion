<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToProduccionDetTable extends Migration
{
    public function up()
    {
        Schema::table('produccion_det', function (Blueprint $table) {
            $table->renameColumn('id_recetas', 'id_recetas_cab');
            $table->unsignedBigInteger('id_recetas_det')->nullable()->after('id_recetas_cab');
            $table->unsignedBigInteger('id_u_medidas_prodcc')->nullable()->after('id_u_medidas');
            $table->decimal('cantidad_esperada', 10, 2)->default(0)->after('cantidad_pedido');
            $table->decimal('subtotal_receta', 10, 2)->default(0)->after('costo_diseño');
            
            $table->foreign('id_recetas_det')->references('id_recetas_det')->on('recetas_det');
            $table->foreign('id_u_medidas_prodcc')->references('id_u_medidas')->on('u_medidas');
        });
    }

    public function down()
    {
        Schema::table('produccion_det', function (Blueprint $table) {
            $table->renameColumn('id_recetas_cab', 'id_recetas');
            $table->dropForeign(['id_recetas_det']);
            $table->dropForeign(['id_u_medidas_prodcc']);
            
            $table->dropColumn(['id_recetas_det', 'id_u_medidas_prodcc', 'cantidad_esperada', 'subtotal_receta']);
        });
    }
}