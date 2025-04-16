<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('equipos_cab', function (Blueprint $table) {
            // Columna principal
            $table->id('id_equipos_cab');
            
            // Relaciones con definición explícita
            $table->unsignedBigInteger('id_usuarios');
            $table->unsignedBigInteger('id_areas');
            $table->unsignedBigInteger('id_turnos');
            
            // Campos de fecha y hora
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_salida')->nullable();
            $table->time('hora_ingreso')->nullable();
            $table->time('hora_salida')->nullable();
            
            // Metadatos
            $table->date('create_date')->nullable();
            $table->date('last_update')->nullable();
            $table->boolean('is_deleted')->default(false);
            
            $table->timestamps();
            
            // Definición completa de claves foráneas
            $table->foreign('id_usuarios')
                  ->references('id_usuarios')
                  ->on('usuarios')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_areas')
                  ->references('id_areas')
                  ->on('areas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_turnos')
                  ->references('id_turnos')
                  ->on('turnos')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            // Índices para mejorar el rendimiento
            $table->index('id_usuarios');
            $table->index('id_areas');
            $table->index('id_turnos');
            $table->index('is_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('equipos_cab', function (Blueprint $table) {
            // Eliminar claves foráneas primero
            $table->dropForeign(['id_usuarios']);
            $table->dropForeign(['id_areas']);
            $table->dropForeign(['id_turnos']);
            
            // Eliminar índices
            $table->dropIndex(['id_usuarios']);
            $table->dropIndex(['id_areas']);
            $table->dropIndex(['id_turnos']);
            $table->dropIndex(['is_deleted']);
        });
        
        Schema::dropIfExists('equipos_cab');
    }
};