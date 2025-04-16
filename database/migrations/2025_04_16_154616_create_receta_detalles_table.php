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
        Schema::create('recetas_det', function (Blueprint $table) {
            // Columna principal
            $table->id('id_recetas_det');
            
            // Relaciones con definición explícita
            $table->unsignedBigInteger('id_recetas_cab');
            $table->unsignedBigInteger('id_productos_api');
            $table->unsignedBigInteger('id_u_medidas');
            
            // Campos de datos
            $table->string('nombre', 20);
            $table->integer('cantidad');
            $table->decimal('costo_unitario', 10, 2)->default(0.00);
            $table->decimal('subtotal_receta', 10, 2)->default(0.00);
            $table->float('constante_crecimientoKg')->nullable();
            $table->float('constante_peso_lata')->nullable();
            
            $table->timestamps();
            
            // Definición completa de claves foráneas
            $table->foreign('id_recetas_cab')
                  ->references('id_recetas')
                  ->on('recetas_cab')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_productos_api')
                  ->references('id_item')
                  ->on('productos')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_u_medidas')
                  ->references('id_u_medidas')
                  ->on('u_medidas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            // Índices para mejorar el rendimiento
            $table->index('id_recetas_cab');
            $table->index('id_productos_api');
            $table->index('id_u_medidas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('recetas_det', function (Blueprint $table) {
            // Eliminar claves foráneas primero
            $table->dropForeign(['id_recetas_cab']);
            $table->dropForeign(['id_productos_api']);
            $table->dropForeign(['id_u_medidas']);
            
            // Eliminar índices
            $table->dropIndex(['id_recetas_cab']);
            $table->dropIndex(['id_productos_api']);
            $table->dropIndex(['id_u_medidas']);
        });
        
        Schema::dropIfExists('recetas_det');
    }
};