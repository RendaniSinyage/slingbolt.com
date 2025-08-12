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
        if (!Schema::hasTable('loan_documents')) {
            Schema::create('loan_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('loan_application_id')->constrained()->onDelete('cascade');
                $table->string('file_name');
                $table->string('file_path');
                $table->integer('file_size');
                $table->integer('created_by');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_documents');
    }
};
