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
        Schema::create('mermas_det', function (Blueprint $table) {
            // Columna principal
            $table->id('id_mermas_det');
            
            // Relaciones con definición explícita
            $table->unsignedBigInteger('id_mermas_cab');
            $table->unsignedBigInteger('id_productos_api');
            $table->unsignedBigInteger('id_u_medidas');
            
            // Campos de datos
            $table->float('cantidad');
            $table->string('obs', 255);
            $table->boolean('is_deleted')->default(false);
            
            $table->timestamps();
            
            // Definición completa de claves foráneas
            $table->foreign('id_mermas_cab')
                  ->references('id_mermas_cab')
                  ->on('mermas_cab')
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
            $table->index('id_mermas_cab');
            $table->index('id_productos_api');
            $table->index('id_u_medidas');
            $table->index('is_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('mermas_det', function (Blueprint $table) {
            // Eliminar claves foráneas en orden inverso
            $table->dropForeign(['id_u_medidas']);
            $table->dropForeign(['id_productos_api']);
            $table->dropForeign(['id_mermas_cab']);
            
            // Eliminar índices
            $table->dropIndex(['id_mermas_cab']);
            $table->dropIndex(['id_productos_api']);
            $table->dropIndex(['id_u_medidas']);
            $table->dropIndex(['is_deleted']);
        });
        
        Schema::dropIfExists('mermas_det');
    }
};