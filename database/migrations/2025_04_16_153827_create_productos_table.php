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
        Schema::create('productos', function (Blueprint $table) {
            $table->integer('id_item')->primary();
            $table->integer('id_area')->nullable();
            $table->string('area', 50)->nullable();
            $table->string('codigo', 50)->nullable();
            $table->string('nombre', 255);
            $table->decimal('costo', 10, 3)->nullable();
            $table->decimal('ref_venta', 10, 3)->nullable();
            $table->decimal('margen', 10, 2)->nullable();
            $table->integer('id_impuesto')->nullable();
            $table->bigInteger('unspsc')->nullable();
            $table->string('impuesto', 50)->nullable();
            $table->integer('id_categoria')->nullable();
            $table->integer('id_presentacion')->nullable();
            $table->string('presentacion', 50)->nullable();
            $table->integer('percepcion')->nullable();
            $table->integer('id_marca')->nullable();
            $table->string('marca', 100)->nullable();
            $table->string('categoria', 100)->nullable();
            $table->integer('id_sub_categoria')->nullable();
            $table->string('sub_categoria', 100)->nullable();
            $table->text('url')->nullable();
            $table->integer('condicion')->nullable();
            $table->integer('id_item_relacion')->nullable();
            $table->decimal('item_cantidad_relacion', 10, 2)->nullable();
            $table->decimal('arti_por', 10, 2)->nullable();
            $table->integer('aplicacion')->nullable();
            $table->integer('estatus_mayor')->nullable();
            $table->decimal('precio_mayor', 10, 3)->nullable();
            $table->decimal('costo_anterior', 10, 3)->nullable();
            $table->string('descuento1', 10)->nullable();
            $table->string('descuento2', 10)->nullable();
            $table->decimal('venta', 10, 3)->nullable();
            $table->timestamp('fecha_actualizacion')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('productos');
    }
};
