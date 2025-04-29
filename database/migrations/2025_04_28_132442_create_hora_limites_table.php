<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hora_limites', function (Blueprint $table) {
            $table->id('id_hora_limite');
            $table->time('hora_limite');
            $table->text('descripcion')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        // Insertar valor por defecto
        DB::table('hora_limites')->insert([
            'hora_limite' => '14:00:00',
            'descripcion' => 'Hora límite por defecto para pedidos',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('hora_limites');
    }
};