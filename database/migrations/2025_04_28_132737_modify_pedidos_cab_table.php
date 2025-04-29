<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pedidos_cab', function (Blueprint $table) {
            // Eliminar columnas
            $table->dropColumn(['created_at', 'updated_at', 'id_estados']);
            
            // Agregar nuevas columnas
            $table->timestamp('deleted_at')->nullable()->after('is_deleted');
            $table->boolean('status')->default(true)->after('is_deleted');
            
            // Cambiar nombre de hora_limite a id_hora_limite (temporalmente)
            $table->renameColumn('hora_limite', 'temp_hora_limite');
            
            // Agregar nueva columna como BIGINT
            $table->unsignedBigInteger('id_hora_limite')->nullable()->after('temp_hora_limite');
        });

        // Convertir valores de tiempo a IDs (asumiendo que ya tienes datos en hora_limites)
        // Esta consulta asume que hay una correspondencia directa entre la hora y el ID en hora_limites
        DB::statement('
            UPDATE pedidos_cab 
            SET id_hora_limite = (
                SELECT id_hora_limite 
                FROM hora_limites 
                WHERE hora_limite = pedidos_cab.temp_hora_limite::time
                LIMIT 1
            )
        ');

        // Si no hay correspondencia, establece un valor por defecto (por ejemplo, 1)
        DB::statement("UPDATE pedidos_cab SET id_hora_limite = 1 WHERE id_hora_limite IS NULL");

        Schema::table('pedidos_cab', function (Blueprint $table) {
            // Eliminar la columna temporal
            $table->dropColumn('temp_hora_limite');
            
            // Hacer la columna no nullable
            $table->unsignedBigInteger('id_hora_limite')->nullable(false)->change();
            
            // Agregar clave foránea
            $table->foreign('id_hora_limite')->references('id_hora_limite')->on('hora_limites');
        });
    }

    public function down()
    {
        Schema::table('pedidos_cab', function (Blueprint $table) {
            $table->dropForeign(['id_hora_limite']);
            
            // Crear columna temporal para la hora
            $table->time('temp_hora_limite')->nullable()->after('id_hora_limite');
        });

        // Convertir IDs de vuelta a horas
        DB::statement('
            UPDATE pedidos_cab 
            SET temp_hora_limite = (
                SELECT hora_limite 
                FROM hora_limites 
                WHERE id_hora_limite = pedidos_cab.id_hora_limite
                LIMIT 1
            )::time
        ');

        Schema::table('pedidos_cab', function (Blueprint $table) {
            // Renombrar y eliminar columnas
            $table->renameColumn('temp_hora_limite', 'hora_limite');
            $table->dropColumn('id_hora_limite');
            
            // Restaurar otras columnas
            $table->dropColumn(['deleted_at', 'status']);
            $table->timestamps();
            $table->unsignedBigInteger('id_estados');
        });
    }
};
