<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dateTime('deleted_at')->nullable()->after('is_deleted');
            // Mantener los campos date existentes y agregar nuevos campos datetime si es necesario
            $table->dateTime('created_at_datetime')->nullable()->after('create_date');
            $table->dateTime('updated_at_datetime')->nullable()->after('last_update');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropColumn(['deleted_at', 'created_at_datetime', 'updated_at_datetime']);
        });
    }
};