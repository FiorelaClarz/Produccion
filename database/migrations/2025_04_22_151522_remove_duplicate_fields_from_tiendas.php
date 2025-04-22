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
        Schema::table('tiendas', function (Blueprint $table) {
            // Eliminar los campos duplicados
            $table->dropColumn(['created_at', 'updated_at']);
            
            // Asegurar que los campos timestamp existan
            if (!Schema::hasColumn('tiendas', 'created_at_datetime')) {
                $table->dateTime('created_at_datetime')->nullable();
            }
            if (!Schema::hasColumn('tiendas', 'updated_at_datetime')) {
                $table->dateTime('updated_at_datetime')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('tiendas', function (Blueprint $table) {
            // Para revertir, volver a agregar los campos
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
        });
    }
};