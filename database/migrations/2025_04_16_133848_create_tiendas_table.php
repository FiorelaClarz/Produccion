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
        Schema::create('tiendas', function (Blueprint $table) {
            $table->id('id_tiendas');
            $table->string('nombre', 45);
            $table->dateTime('created_at_datetime')->nullable();
            $table->dateTime('updated_at_datetime')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('is_deleted')->default(false);
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
        Schema::dropIfExists('tiendas');
    }
};
