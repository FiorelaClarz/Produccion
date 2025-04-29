<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToEquiposDetTable extends Migration
{
    public function up()
    {
        Schema::table('equipos_det', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->after('is_deleted');
        });
    }

    public function down()
    {
        Schema::table('equipos_det', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
}