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
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->decimal('monthly_income', 15, 2)->nullable()->after('is_secured_loan');
            $table->decimal('monthly_debt', 15, 2)->nullable()->after('monthly_income');
            $table->json('failed_debit_orders_last_3_months')->nullable()->after('monthly_debt');
            $table->integer('reversed_debit_orders_last_3_months')->nullable()->after('failed_debit_orders_last_3_months');
            $table->string('recommendation')->nullable()->after('reversed_debit_orders_last_3_months');
            $table->string('recommendation_reason')->nullable()->after('recommendation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropColumn('monthly_income');
            $table->dropColumn('monthly_debt');
            $table->dropColumn('failed_debit_orders_last_3_months');
            $table->dropColumn('reversed_debit_orders_last_3_months');
            $table->dropColumn('recommendation');
            $table->dropColumn('recommendation_reason');
        });
    }
};
