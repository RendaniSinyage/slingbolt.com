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
        Schema::create('loan_security_pledges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_security_assignment_id')->constrained('loan_security_assignments')->onDelete('cascade');
            $table->foreignId('loan_security_id')->constrained('loan_securities')->onDelete('cascade');

            // This could be a quantity or an amount, depending on the security type.
            // Using a decimal for flexibility.
            $table->decimal('quantity_pledged', 15, 2);

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
        Schema::dropIfExists('loan_security_pledges');
    }
};
