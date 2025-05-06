<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToTurnosTable extends Migration
{
    public function up()
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_horas_limite')->nullable()->after('nombre');
            
            $table->foreign('id_horas_limite')->references('id_hora_limite')->on('hora_limites');
        });
    }

    public function down()
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->dropForeign(['id_horas_limite']);
            $table->dropColumn('id_horas_limite');
        });
    }
}