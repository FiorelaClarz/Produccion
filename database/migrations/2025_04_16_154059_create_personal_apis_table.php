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
        Schema::create('personal_api', function (Blueprint $table) {
            // Columna principal
            $table->id('id_personal_api');
            
            // Datos personales
            $table->string('codigo_personal', 45)->unique();
            $table->string('dni_personal', 45)->unique();
            $table->string('nombre', 45);
            
            // Relaciones
            $table->unsignedBigInteger('id_areas');
            $table->unsignedBigInteger('id_tiendas_api');
            
            // Datos económicos
            $table->decimal('sueldo', 10, 2)->nullable();
            
            // Timestamps automáticos
            $table->timestamps();
            
            // Claves foráneas
            $table->foreign('id_areas')
                  ->references('id_areas')
                  ->on('areas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
                  
            $table->foreign('id_tiendas_api')
                  ->references('id_tiendas')
                  ->on('tiendas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            
            // Índices
            $table->index('codigo_personal');
            $table->index('dni_personal');
            $table->index('id_areas');
            $table->index('id_tiendas_api');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('personal_api', function (Blueprint $table) {
            // Eliminar claves foráneas
            $table->dropForeign(['id_areas']);
            $table->dropForeign(['id_tiendas_api']);
            
            // Eliminar índices
            $table->dropIndex(['codigo_personal']);
            $table->dropIndex(['dni_personal']);
            $table->dropIndex(['id_areas']);
            $table->dropIndex(['id_tiendas_api']);
        });
        
        Schema::dropIfExists('personal_api');
    }
};