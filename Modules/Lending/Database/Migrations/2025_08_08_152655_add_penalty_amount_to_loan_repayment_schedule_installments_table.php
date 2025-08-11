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
        Schema::table('loan_repayment_schedule_installments', function (Blueprint $table) {
            $table->decimal('penalty_amount', 15, 2)->default(0)->after('interest_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_repayment_schedule_installments', function (Blueprint $table) {
            $table->dropColumn('penalty_amount');
        });
    }
};
