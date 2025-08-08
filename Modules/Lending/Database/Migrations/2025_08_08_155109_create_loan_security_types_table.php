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
        Schema::create('loan_security_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('haircut', 5, 2)->default(0); // e.g., 25.00 for 25%
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
        Schema::dropIfExists('loan_security_types');
    }
};
