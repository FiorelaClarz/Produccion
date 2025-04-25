<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE recetas_cab ALTER COLUMN nombre TYPE VARCHAR(200)');
    }
    
    public function down()
    {
        DB::statement('ALTER TABLE recetas_cab ALTER COLUMN nombre TYPE VARCHAR(20)');
    }
};
