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
        Schema::table('recetas_cab', function (Blueprint $table) {
            $table->decimal('costo_receta', 10, 2)->default(0)->comment('Costo total de elaboración de la receta');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recetas_cab', function (Blueprint $table) {
            $table->dropColumn('costo_receta');
        });
    }
};
