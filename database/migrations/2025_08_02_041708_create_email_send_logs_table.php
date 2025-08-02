<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('email_send_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('email_template');
            $table->string('user_email');
            $table->json('context')->nullable(); // Store email variables
            $table->boolean('sent_successfully')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'email_template'], 'unique_user_template');
            $table->index(['email_template', 'sent_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_send_logs');
    }
};