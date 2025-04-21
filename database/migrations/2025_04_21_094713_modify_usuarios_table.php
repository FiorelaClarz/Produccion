<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Agregar columna deleted_at
            $table->timestamp('deleted_at')->nullable();
            
            // Agregar columna nombre_personal
            $table->string('nombre_personal', 45)->after('id_personal_api');
            
            // Modificar columnas existentes si es necesario
            $table->boolean('status')->default(true)->change();
            $table->boolean('is_deleted')->default(false)->change();
        });

        // Actualizar los datos existentes con los nombres del personal
        DB::statement("
            UPDATE usuarios u 
            SET nombre_personal = (
                SELECT nombre FROM personal_api p 
                WHERE p.id_personal_api = u.id_personal_api
            )
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Revertir los cambios
            $table->dropColumn('deleted_at');
            $table->dropColumn('nombre_personal');
            
            // Revertir los cambios de columnas modificadas
            $table->boolean('status')->change();
            $table->boolean('is_deleted')->change();
        });
    }
}