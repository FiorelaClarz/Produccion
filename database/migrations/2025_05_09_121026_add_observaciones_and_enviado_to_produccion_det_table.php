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
    // En el archivo de migración generado
public function up()
{
    Schema::table('produccion_det', function (Blueprint $table) {
        $table->text('observaciones')->nullable()->after('cant_harina');
        $table->boolean('es_enviado')->default(false)->after('observaciones');
    });
}

public function down()
{
    Schema::table('produccion_det', function (Blueprint $table) {
        $table->dropColumn(['observaciones', 'es_enviado']);
    });
}
};
