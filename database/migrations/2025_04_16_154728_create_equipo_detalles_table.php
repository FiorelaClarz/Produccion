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
        Schema::create('equipos_det', function (Blueprint $table) {
            // Columna principal
            $table->id('id_equipos_det');
            
            // Relaciones con definición explícita
            $table->unsignedBigInteger('id_equipos_cab');
            $table->unsignedBigInteger('id_personal_api');
            
            // Campos de estado
            $table->boolean('status')->default(true);
            $table->boolean('is_deleted')->default(false);
            
            $table->timestamps();
            
            // Definición completa de claves foráneas
            $table->foreign('id_equipos_cab')
                  ->references('id_equipos_cab')
                  ->on('equipos_cab')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_personal_api')
                  ->references('id_personal_api')
                  ->on('personal_api')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            // Índices para mejorar el rendimiento
            $table->index('id_equipos_cab');
            $table->index('id_personal_api');
            $table->index(['status', 'is_deleted']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('equipos_det', function (Blueprint $table) {
            // Eliminar claves foráneas primero
            $table->dropForeign(['id_equipos_cab']);
            $table->dropForeign(['id_personal_api']);
            
            // Eliminar índices
            $table->dropIndex(['id_equipos_cab']);
            $table->dropIndex(['id_personal_api']);
            $table->dropIndex(['status', 'is_deleted']);
        });
        
        Schema::dropIfExists('equipos_det');
    }
};