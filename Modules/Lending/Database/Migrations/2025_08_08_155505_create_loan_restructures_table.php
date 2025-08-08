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
        Schema::create('loan_restructures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');

            $table->string('status')->default('Initiated'); // Initiated, Approved, Rejected
            $table->date('restructure_date');

            $table->decimal('old_rate_of_interest', 8, 4);
            $table->integer('old_repayment_periods');

            $table->decimal('new_rate_of_interest', 8, 4);
            $table->integer('new_repayment_periods');

            $table->text('reason')->nullable();

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
        Schema::dropIfExists('loan_restructures');
    }
};
