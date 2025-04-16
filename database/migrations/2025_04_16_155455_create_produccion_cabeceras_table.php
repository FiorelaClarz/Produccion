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
        Schema::create('produccion_cab', function (Blueprint $table) {
            // Columna principal
            $table->id('id_produccion_cab');
            
            // Relación con definición explícita
            $table->unsignedBigInteger('id_equipos');
            
            // Campos de fecha/hora y documento
            $table->date('fecha');
            $table->time('hora');
            $table->string('doc_interno', 40)->nullable();
            
            $table->timestamps();
            
            // Definición completa de clave foránea
            $table->foreign('id_equipos')
                  ->references('id_equipos_cab')
                  ->on('equipos_cab')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            // Índices para mejorar el rendimiento
            $table->index('id_equipos');
            $table->index('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('produccion_cab', function (Blueprint $table) {
            // Eliminar clave foránea primero
            $table->dropForeign(['id_equipos']);
            
            // Eliminar índices
            $table->dropIndex(['id_equipos']);
            $table->dropIndex(['fecha']);
        });
        
        Schema::dropIfExists('produccion_cab');
    }
};