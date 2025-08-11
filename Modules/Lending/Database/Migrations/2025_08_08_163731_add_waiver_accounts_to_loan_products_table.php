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
        Schema::table('loan_products', function (Blueprint $table) {
            $table->unsignedBigInteger('interest_waiver_account_id')->nullable()->after('suspense_interest_income_id');
            $table->unsignedBigInteger('penalty_waiver_account_id')->nullable()->after('interest_waiver_account_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropColumn(['interest_waiver_account_id', 'penalty_waiver_account_id']);
        });
    }
};
