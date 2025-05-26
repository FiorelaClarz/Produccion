<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mermas_det', function (Blueprint $table) {
            // Añadimos relación con áreas
            $table->unsignedBigInteger('id_areas')->nullable()->after('id_mermas_cab');
            $table->foreign('id_areas')->references('id_areas')->on('areas');
            
            // Añadimos relación con recetas
            $table->unsignedBigInteger('id_recetas')->nullable()->after('id_areas');
            $table->foreign('id_recetas')->references('id_recetas')->on('recetas_cab');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mermas_det', function (Blueprint $table) {
            // Eliminamos las relaciones y columnas
            $table->dropForeign(['id_areas']);
            $table->dropForeign(['id_recetas']);
            $table->dropColumn(['id_areas', 'id_recetas']);
        });
    }
};
