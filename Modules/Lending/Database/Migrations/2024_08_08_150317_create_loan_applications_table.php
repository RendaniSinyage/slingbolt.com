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
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id();
            $table->integer('created_by')->default(0);

            $table->morphs('applicant');

            $table->foreignId('loan_product_id')->constrained()->onDelete('cascade');

            $table->string('status')->default('Open'); // Open, Approved, Rejected
            $table->decimal('loan_amount', 15, 2);

            $table->string('repayment_method')->nullable();
            $table->integer('repayment_periods')->nullable();

            $table->boolean('is_secured_loan')->default(false);

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
        Schema::dropIfExists('loan_applications');
    }
};
