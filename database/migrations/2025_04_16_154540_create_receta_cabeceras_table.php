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
        Schema::create('recetas_cab', function (Blueprint $table) {
            // Columna principal
            $table->id('id_recetas');
            
            // Relaciones con definición explícita
            $table->unsignedBigInteger('id_areas');
            $table->unsignedBigInteger('id_productos_api');
            $table->unsignedBigInteger('id_u_medidas');
            
            // Campos de datos
            $table->string('nombre', 20);
            $table->date('create_date')->nullable();
            $table->date('last_update')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->float('cant_rendimiento')->default(0.0);
            
            $table->timestamps();
            
            // Definición completa de claves foráneas
            $table->foreign('id_areas')
                  ->references('id_areas')
                  ->on('areas')
                  ->onDelete('restrict')
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
            $table->index('id_areas');
            $table->index('id_productos_api');
            $table->index('id_u_medidas');
            $table->index('status');
            $table->index('is_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('recetas_cab', function (Blueprint $table) {
            // Eliminar claves foráneas primero
            $table->dropForeign(['id_areas']);
            $table->dropForeign(['id_productos_api']);
            $table->dropForeign(['id_u_medidas']);
            
            // Eliminar índices
            $table->dropIndex(['id_areas']);
            $table->dropIndex(['id_productos_api']);
            $table->dropIndex(['id_u_medidas']);
            $table->dropIndex(['status']);
            $table->dropIndex(['is_deleted']);
        });
        
        Schema::dropIfExists('recetas_cab');
    }
};