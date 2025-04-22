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
        Schema::table('areas', function (Blueprint $table) {
            // Eliminar las columnas de timestamps de Laravel que no usas
            $table->dropColumn(['created_at', 'updated_at']);
            
            // Opcional: También puedes eliminar los campos datetime adicionales si no los necesitas
            // $table->dropColumn(['created_at_datetime', 'updated_at_datetime']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('areas', function (Blueprint $table) {
            // Para revertir, volvemos a agregar las columnas (opcional)
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
};