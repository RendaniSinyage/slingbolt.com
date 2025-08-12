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
        if (!Schema::hasTable('loan_repayment_schedule_installments')) {
            Schema::create('loan_repayment_schedule_installments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('loan_repayment_schedule_id')->constrained()->onDelete('cascade');

                $table->date('payment_date');
                $table->decimal('principal_amount', 15, 2);
                $table->decimal('interest_amount', 15, 2);
                $table->decimal('outstanding_principal_balance', 15, 2);

                $table->decimal('amount_paid', 15, 2)->default(0);
                $table->boolean('is_paid')->default(false);

                $table->timestamps();
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
        Schema::dropIfExists('loan_repayment_schedule_installments');
    }
};