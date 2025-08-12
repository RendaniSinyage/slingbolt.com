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
                $table->unsignedBigInteger('loan_application_id');
                $table->string('file_name');
                $table->string('file_path');
                $table->integer('file_size');
                $table->integer('created_by');
                $table->timestamps();
                
                // Add foreign key constraint only if loan_applications table exists
                if (Schema::hasTable('loan_applications')) {
                    $table->foreign('loan_application_id')->references('id')->on('loan_applications')->onDelete('cascade');
                }
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