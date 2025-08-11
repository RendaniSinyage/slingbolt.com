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
        Schema::create('loan_product_loan_partner', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_product_id')->constrained()->onDelete('cascade');
            $table->foreignId('loan_partner_id')->constrained()->onDelete('cascade');
            $table->decimal('share_percentage', 5, 2);
            $table->timestamps();

            $table->unique(['loan_product_id', 'loan_partner_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_product_loan_partner');
    }
};
