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
            
            // Relaciones - especificando tipo exacto para coincidir con las tablas referenciadas
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
            
            $table->timestamps();
            
            // Claves foráneas CORREGIDAS (especificando columnas exactas)
            $table->foreign('id_personal_api')
                  ->references('id_personal_api')  // Columna en la tabla referenciada
                  ->on('personal_api')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_tiendas_api')
                  ->references('id_tiendas')  // Columna en la tabla referenciada
                  ->on('tiendas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_areas')
                  ->references('id_areas')  // Columna en la tabla referenciada
                  ->on('areas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_roles')
                  ->references('id_roles')  // Columna en la tabla referenciada
                  ->on('rols')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Eliminar claves foráneas en orden inverso
            $table->dropForeign(['id_roles']);
            $table->dropForeign(['id_areas']);
            $table->dropForeign(['id_tiendas_api']);
            $table->dropForeign(['id_personal_api']);
        });
        
        Schema::dropIfExists('usuarios');
    }
};