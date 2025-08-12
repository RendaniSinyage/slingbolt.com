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
        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->string('ocid')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status');
            $table->string('main_procurement_category');
            $table->text('additional_procurement_categories')->nullable();
            $table->text('submission_method')->nullable();
            $table->string('procuring_entity_name');
            $table->string('procuring_entity_id');
            $table->timestamp('tender_period_start_date')->nullable();
            $table->timestamp('tender_period_end_date')->nullable();
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
        Schema::dropIfExists('tenders');
    }
};
