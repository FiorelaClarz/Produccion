<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCodigoTiendaColumnToTiendasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tiendas', function (Blueprint $table) {
            $table->string('codigo_tienda', 10)->nullable()->after('nombre');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tiendas', function (Blueprint $table) {
            $table->dropColumn('codigo_tienda');
        });
    }
}
