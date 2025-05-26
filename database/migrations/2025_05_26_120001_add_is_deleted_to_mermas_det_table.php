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
        Schema::table('mermas_det', function (Blueprint $table) {
            if (!Schema::hasColumn('mermas_det', 'is_deleted')) {
                $table->boolean('is_deleted')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mermas_det', function (Blueprint $table) {
            if (Schema::hasColumn('mermas_det', 'is_deleted')) {
                $table->dropColumn('is_deleted');
            }
        });
    }
};
