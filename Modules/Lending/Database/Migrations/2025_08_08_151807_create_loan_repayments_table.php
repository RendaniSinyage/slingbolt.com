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
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->integer('created_by')->default(0);
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');

            $table->decimal('amount_paid', 15, 2);
            $table->date('payment_date');
            $table->text('remarks')->nullable();

            // Assuming journal_entries table exists in the main app
            $table->unsignedBigInteger('journal_entry_id')->nullable();

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
        Schema::dropIfExists('loan_repayments');
    }
};
