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
        Schema::create('loan_security_assignments', function (Blueprint $table) {
            $table->id();
            $table->integer('created_by')->default(0);

            $table->morphs('assignable'); // For Loan or LoanApplication

            $table->string('status')->default('Pledge Requested');
            $table->decimal('total_security_value', 15, 2);
            $table->decimal('maximum_loan_value', 15, 2);

            $table->dateTime('pledge_time')->nullable();
            $table->dateTime('release_time')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_security_assignments');
    }
};
