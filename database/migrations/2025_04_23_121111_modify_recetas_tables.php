<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyRecetasTables extends Migration
{
    public function up()
    {
        // Modificar recetas_cab
        Schema::table('recetas_cab', function (Blueprint $table) {
            $table->dropColumn(['create_date', 'last_update']);
            $table->float('constante_crecimiento')->default(0);
            $table->float('constante_peso_lata')->default(0);
            $table->softDeletes();
        });

        // Modificar recetas_det
        Schema::table('recetas_det', function (Blueprint $table) {
            $table->dropColumn(['constante_crecimientoKg', 'constante_peso_lata']);
            $table->integer('cant_presentacion')->default(1);
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('recetas_cab', function (Blueprint $table) {
            $table->date('create_date');
            $table->date('last_update');
            $table->dropColumn(['constante_crecimiento', 'constante_peso_lata', 'deleted_at']);
        });

        Schema::table('recetas_det', function (Blueprint $table) {
            $table->float('constante_crecimientoKg')->default(0);
            $table->float('constante_peso_lata')->default(0);
            $table->dropColumn(['cant_presentacion', 'deleted_at']);
        });
    }
}