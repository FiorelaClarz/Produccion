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
        Schema::table('u_medidas', function (Blueprint $table) {
            $table->boolean('status')->default(true); // Por defecto "activo"
            $table->softDeletes(); // Agrega deleted_at para soft delete
        });
    }

    public function down()
    {
        Schema::table('u_medidas', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropSoftDeletes();
        });
    }
};
