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
        Schema::create('pedidos_cab', function (Blueprint $table) {
            // Columna principal
            $table->id('id_pedidos_cab');
            
            // Relaciones - especificando explícitamente las columnas referenciadas
            $table->unsignedBigInteger('id_usuarios');
            $table->unsignedBigInteger('id_tiendas_api');
            $table->unsignedBigInteger('id_estados')->nullable();
            
            // Campos de fecha y hora
            $table->date('fecha_created')->nullable();
            $table->date('fecha_last_update')->nullable();
            $table->time('hora_created')->nullable();
            $table->time('hora_last_update')->nullable();
            
            // Campos adicionales
            $table->boolean('esta_dentro_de_hora')->nullable();
            $table->time('hora_limite')->nullable();
            $table->string('doc_interno', 40)->nullable();
            $table->boolean('is_deleted')->default(false);
            
            $table->timestamps();
            
            // Definición explícita de claves foráneas
            $table->foreign('id_usuarios')
                  ->references('id_usuarios')  // Columna exacta en la tabla usuarios
                  ->on('usuarios')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_tiendas_api')
                  ->references('id_tiendas')  // Columna exacta en la tabla tiendas
                  ->on('tiendas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_estados')
                  ->references('id_estados')  // Columna exacta en la tabla estados
                  ->on('estados')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('pedidos_cab', function (Blueprint $table) {
            // Eliminar claves foráneas en orden inverso
            $table->dropForeign(['id_estados']);
            $table->dropForeign(['id_tiendas_api']);
            $table->dropForeign(['id_usuarios']);
        });
        
        Schema::dropIfExists('pedidos_cab');
    }
};