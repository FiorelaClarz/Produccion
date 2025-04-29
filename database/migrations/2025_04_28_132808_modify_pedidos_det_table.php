<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pedidos_det', function (Blueprint $table) {
            // Agregar nuevas columnas
            $table->unsignedBigInteger('id_estados')->after('is_deleted');
            $table->string('foto_referencial_url')->nullable()->after('foto_referencial');
            
            // Agregar clave foránea
            $table->foreign('id_estados')->references('id_estados')->on('estados');
        });
    }

    public function down()
    {
        Schema::table('pedidos_det', function (Blueprint $table) {
            $table->dropForeign(['id_estados']);
            $table->dropColumn(['id_estados', 'foto_referencial_url']);
        });
    }
};