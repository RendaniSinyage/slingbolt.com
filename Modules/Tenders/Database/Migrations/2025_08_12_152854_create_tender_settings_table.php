<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tender_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->text('categories')->nullable();
            $table->text('provinces')->nullable();
            $table->string('type')->nullable();
            $table->string('submission_type')->default('esubmission');
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
        Schema::dropIfExists('tender_settings');
    }
};
