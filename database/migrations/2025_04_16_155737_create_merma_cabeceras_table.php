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
        Schema::create('mermas_cab', function (Blueprint $table) {
            // Columna principal
            $table->id('id_mermas_cab');
            
            // Relaciones con definición explícita
            $table->unsignedBigInteger('id_usuarios');
            $table->unsignedBigInteger('id_tiendas_api');
            
            // Campos de fecha/hora
            $table->date('fecha_registro')->nullable();
            $table->time('hora_registro')->nullable();
            $table->date('last_update')->nullable();
            
            $table->timestamps();
            
            // Definición completa de claves foráneas
            $table->foreign('id_usuarios')
                  ->references('id_usuarios')
                  ->on('usuarios')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_tiendas_api')
                  ->references('id_tiendas')
                  ->on('tiendas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            // Índices para mejorar el rendimiento
            $table->index('id_usuarios');
            $table->index('id_tiendas_api');
            $table->index('fecha_registro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('mermas_cab', function (Blueprint $table) {
            // Eliminar claves foráneas primero
            $table->dropForeign(['id_usuarios']);
            $table->dropForeign(['id_tiendas_api']);
            
            // Eliminar índices
            $table->dropIndex(['id_usuarios']);
            $table->dropIndex(['id_tiendas_api']);
            $table->dropIndex(['fecha_registro']);
        });
        
        Schema::dropIfExists('mermas_cab');
    }
};