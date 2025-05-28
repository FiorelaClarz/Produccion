<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade las columnas 'costo' y 'total' a la tabla mermas_det
     * para almacenar el costo unitario del producto y el total (cantidad * costo)
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mermas_det', function (Blueprint $table) {
            $table->decimal('costo', 10, 2)->nullable()->after('cantidad')->comment('Costo unitario del producto');
            $table->decimal('total', 10, 2)->nullable()->after('costo')->comment('Total calculado (cantidad * costo)');
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
            $table->dropColumn('costo');
            $table->dropColumn('total');
        });
    }
};


