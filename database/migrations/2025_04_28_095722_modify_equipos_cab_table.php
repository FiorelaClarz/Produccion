<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyEquiposCabTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipos_cab', function (Blueprint $table) {
            // Eliminar columnas
            $table->dropColumn(['fecha_ingreso', 'fecha_salida', 'hora_ingreso', 'hora_salida', 'create_date', 'last_update']);
            
            // Agregar nuevas columnas
            $table->boolean('status')->default(true)->after('is_deleted');
            $table->timestamp('deleted_at')->nullable()->after('status');
            $table->timestamp('salida')->nullable()->after('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipos_cab', function (Blueprint $table) {
            // Revertir cambios (no siempre es posible revertir dropColumn)
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_salida')->nullable();
            $table->time('hora_ingreso')->nullable();
            $table->time('hora_salida')->nullable();
            $table->date('create_date')->nullable();
            $table->date('last_update')->nullable();
            
            $table->dropColumn(['status', 'deleted_at', 'salida']);
        });
    }
}