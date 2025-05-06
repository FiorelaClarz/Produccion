<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToProduccionCabTable extends Migration
{
    public function up()
    {
        Schema::table('produccion_cab', function (Blueprint $table) {
            $table->unsignedBigInteger('id_turnos')->nullable()->after('id_equipos');
            $table->unsignedBigInteger('id_usuario')->nullable()->after('id_turnos');
            
            $table->foreign('id_turnos')->references('id_turnos')->on('turnos');
            $table->foreign('id_usuario')->references('id_usuarios')->on('usuarios');
        });
    }

    public function down()
    {
        Schema::table('produccion_cab', function (Blueprint $table) {
            $table->dropForeign(['id_turnos']);
            $table->dropForeign(['id_usuario']);
            
            $table->dropColumn(['id_turnos', 'id_usuario']);
        });
    }
}