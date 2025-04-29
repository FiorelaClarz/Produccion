<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pedidos_det', function (Blueprint $table) {
            $table->unsignedBigInteger('id_recetas')->nullable()->after('id_areas');
            
            $table->foreign('id_recetas')
                  ->references('id_recetas')
                  ->on('recetas_cab')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('pedidos_det', function (Blueprint $table) {
            $table->dropForeign(['id_recetas']);
            $table->dropColumn('id_recetas');
        });
    }
};