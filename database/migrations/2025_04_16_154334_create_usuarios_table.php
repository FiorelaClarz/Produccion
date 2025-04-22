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
        Schema::create('usuarios', function (Blueprint $table) {
            // Columna principal
            $table->id('id_usuarios');
            
            // Relaciones
            $table->unsignedBigInteger('id_personal_api');
            $table->unsignedBigInteger('id_tiendas_api');
            $table->unsignedBigInteger('id_areas');
            $table->unsignedBigInteger('id_roles');
            
            // Datos de autenticación
            $table->string('clave', 255);
            
            // Metadatos y estado
            $table->date('create_date')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->date('last_update')->nullable();
            
            // Timestamps automáticos
            $table->timestamps();
            
            // Claves foráneas
            $table->foreign('id_personal_api')
                  ->references('id_personal_api')
                  ->on('personal_api')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_tiendas_api')
                  ->references('id_tiendas')
                  ->on('tiendas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_areas')
                  ->references('id_areas')
                  ->on('areas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_roles')
                  ->references('id_roles')
                  ->on('rols')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            // Índices
            $table->index('id_personal_api');
            $table->index('id_tiendas_api');
            $table->index('id_areas');
            $table->index('id_roles');
            $table->index('status');
            $table->index('is_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Eliminar claves foráneas
            $table->dropForeign(['id_personal_api']);
            $table->dropForeign(['id_tiendas_api']);
            $table->dropForeign(['id_areas']);
            $table->dropForeign(['id_roles']);
            
            // Eliminar índices
            $table->dropIndex(['id_personal_api']);
            $table->dropIndex(['id_tiendas_api']);
            $table->dropIndex(['id_areas']);
            $table->dropIndex(['id_roles']);
            $table->dropIndex(['status']);
            $table->dropIndex(['is_deleted']);
        });
        
        Schema::dropIfExists('usuarios');
    }
};