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
        Schema::create('loan_securities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('loan_security_type_id')->constrained('loan_security_types')->onDelete('cascade');

            $table->string('loan_security_code')->unique();
            $table->string('loan_security_name');

            $table->decimal('original_security_value', 15, 2);
            $table->decimal('utilized_security_value', 15, 2)->default(0);

            $table->boolean('disabled')->default(false);

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
        Schema::dropIfExists('loan_securities');
    }
};
