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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');

            // Using morphs for a polymorphic relationship to the applicant (Customer, Member, etc.)
            $table->morphs('applicant');

            // Assuming loan_products and loan_applications tables will be created
            $table->foreignId('loan_product_id');
            $table->foreignId('loan_application_id')->nullable();

            $table->string('status')->default('Sanctioned');
            $table->decimal('loan_amount', 15, 2);
            $table->decimal('disbursed_amount', 15, 2)->default(0);
            $table->decimal('rate_of_interest', 8, 4);
            $table->decimal('penalty_charges_rate', 8, 4)->nullable();

            $table->date('posting_date');
            $table->date('disbursement_date')->nullable();
            $table->date('closure_date')->nullable();
            $table->date('settlement_date')->nullable();

            $table->string('repayment_method')->nullable();
            $table->integer('repayment_periods')->nullable();
            $table->string('repayment_frequency')->nullable();


            $table->boolean('is_secured_loan')->default(false);
            $table->boolean('is_term_loan')->default(false);
            $table->boolean('is_npa')->default(false);

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
        Schema::dropIfExists('loans');
    }
};
