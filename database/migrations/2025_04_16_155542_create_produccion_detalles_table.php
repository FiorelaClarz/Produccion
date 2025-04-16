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
        Schema::create('produccion_det', function (Blueprint $table) {
            // Columna principal
            $table->id('id_produccion_det');
            
            // Relaciones con definición explícita
            $table->unsignedBigInteger('id_produccion_cab');
            $table->unsignedBigInteger('id_productos_api');
            $table->unsignedBigInteger('id_u_medidas');
            $table->unsignedBigInteger('id_recetas');
            $table->unsignedBigInteger('id_areas');
            
            // Campos de producción
            $table->integer('cantidad_pedido');
            $table->integer('cantidad_producida_real');
            $table->boolean('es_iniciado')->default(false);
            $table->boolean('es_terminado')->default(false);
            $table->boolean('es_cancelado')->default(false);
            
            // Campos financieros
            $table->decimal('costo_diseño', 10, 2)->default(0.00);
            $table->decimal('total_receta', 10, 2)->default(0.00);
            $table->float('cant_harina')->default(0.0);
            
            $table->timestamps();
            
            // Definición completa de claves foráneas
            $table->foreign('id_produccion_cab')
                  ->references('id_produccion_cab')
                  ->on('produccion_cab')
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
                  
            $table->foreign('id_recetas')
                  ->references('id_recetas')
                  ->on('recetas_cab')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_areas')
                  ->references('id_areas')
                  ->on('areas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            // Índices para mejorar el rendimiento
            $table->index('id_produccion_cab');
            $table->index('id_productos_api');
            $table->index('id_u_medidas');
            $table->index('id_recetas');
            $table->index('id_areas');
            $table->index(['es_iniciado', 'es_terminado', 'es_cancelado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('produccion_det', function (Blueprint $table) {
            // Eliminar claves foráneas en orden inverso
            $table->dropForeign(['id_areas']);
            $table->dropForeign(['id_recetas']);
            $table->dropForeign(['id_u_medidas']);
            $table->dropForeign(['id_productos_api']);
            $table->dropForeign(['id_produccion_cab']);
            
            // Eliminar índices
            $table->dropIndex(['id_produccion_cab']);
            $table->dropIndex(['id_productos_api']);
            $table->dropIndex(['id_u_medidas']);
            $table->dropIndex(['id_recetas']);
            $table->dropIndex(['id_areas']);
            $table->dropIndex(['es_iniciado', 'es_terminado', 'es_cancelado']);
        });
        
        Schema::dropIfExists('produccion_det');
    }
};