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
        if (Schema::hasTable('plans')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->integer('tenders')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('plans', 'tenders')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->dropColumn('tenders');
            });
        }
    }
};
