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
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');

            $table->string('product_code')->unique();
            $table->string('product_name');

            $table->decimal('rate_of_interest', 8, 4);
            $table->decimal('penalty_interest_rate', 8, 4)->nullable();
            $table->decimal('maximum_loan_amount', 15, 2)->nullable();
            $table->integer('days_past_due_threshold_for_npa')->nullable();

            $table->boolean('is_term_loan')->default(false);
            $table->boolean('disabled')->default(false);

            // Accounting fields - assuming 'accounts' table exists
            $table->unsignedBigInteger('disbursement_account_id');
            $table->unsignedBigInteger('payment_account_id');
            $table->unsignedBigInteger('loan_account_id');
            $table->unsignedBigInteger('interest_income_account_id');
            $table->unsignedBigInteger('penalty_income_account_id');
            $table->unsignedBigInteger('write_off_account_id');
            $table->unsignedBigInteger('interest_receivable_account_id');
            $table->unsignedBigInteger('penalty_receivable_account_id');
            $table->unsignedBigInteger('suspense_interest_income_id')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_products');
    }
};
