<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('recetas_instructivos', function (Blueprint $table) {
            $table->id('id_recetas_instructivos');
            $table->foreignId('id_recetas')->constrained('recetas_cab', 'id_recetas');
            $table->string('titulo', 200);
            $table->json('instrucciones'); // Almacenará los pasos como JSON
            $table->integer('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('id_recetas');
        });
    }

    public function down()
    {
        Schema::dropIfExists('recetas_instructivos');
    }
};