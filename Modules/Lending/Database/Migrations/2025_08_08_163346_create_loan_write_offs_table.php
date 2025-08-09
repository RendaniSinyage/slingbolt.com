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
        Schema::create('loan_write_offs', function (Blueprint $table) {
            $table->id();
            $table->integer('created_by')->default(0);
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');

            $table->date('write_off_date');
            $table->decimal('write_off_amount', 15, 2);
            $table->text('remarks')->nullable();

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
        Schema::dropIfExists('loan_write_offs');
    }
};
