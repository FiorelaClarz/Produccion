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
        Schema::create('pedidos_det', function (Blueprint $table) {
            // Columna principal
            $table->id('id_pedidos_det');
            
            // Relaciones con definición explícita
            $table->unsignedBigInteger('id_pedidos_cab');
            $table->unsignedBigInteger('id_productos_api');
            $table->unsignedBigInteger('id_areas');
            $table->unsignedBigInteger('id_u_medidas');
            
            // Campos de datos
            $table->float('cantidad');
            $table->boolean('es_personalizado')->default(false);
            $table->text('descripcion')->nullable();
            $table->text('foto_referencial')->nullable();
            $table->boolean('is_deleted')->default(false);
            
            $table->timestamps();
            
            // Definición completa de claves foráneas
            $table->foreign('id_pedidos_cab')
                  ->references('id_pedidos_cab')
                  ->on('pedidos_cab')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_productos_api')
                  ->references('id_item')
                  ->on('productos')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_areas')
                  ->references('id_areas')
                  ->on('areas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_u_medidas')
                  ->references('id_u_medidas')
                  ->on('u_medidas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            // Índices para mejorar el rendimiento
            $table->index('id_pedidos_cab');
            $table->index('id_productos_api');
            $table->index('id_areas');
            $table->index('id_u_medidas');
            $table->index('is_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('pedidos_det', function (Blueprint $table) {
            // Eliminar claves foráneas primero
            $table->dropForeign(['id_pedidos_cab']);
            $table->dropForeign(['id_productos_api']);
            $table->dropForeign(['id_areas']);
            $table->dropForeign(['id_u_medidas']);
            
            // Eliminar índices
            $table->dropIndex(['id_pedidos_cab']);
            $table->dropIndex(['id_productos_api']);
            $table->dropIndex(['id_areas']);
            $table->dropIndex(['id_u_medidas']);
            $table->dropIndex(['is_deleted']);
        });
        
        Schema::dropIfExists('pedidos_det');
    }
};