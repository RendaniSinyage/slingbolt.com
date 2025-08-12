<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('compliance_settings')) {
            Schema::create('compliance_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                
                $table->decimal('max_interest_rate', 8, 4)->nullable();
                $table->decimal('max_initiation_fee', 15, 2)->nullable();
                $table->decimal('max_monthly_service_fee', 15, 2)->nullable();
                
                $table->timestamps();

                $table->unique('company_id');
                
                // Add foreign key constraint only if companies table exists
                if (Schema::hasTable('companies')) {
                    $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('compliance_settings');
    }
};