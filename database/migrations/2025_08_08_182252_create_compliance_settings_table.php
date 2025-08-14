<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('compliance_settings', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            
            \$table->decimal('max_interest_rate', 8, 4)->nullable();
            \$table->decimal('max_initiation_fee', 15, 2)->nullable();
            \$table->decimal('max_monthly_service_fee', 15, 2)->nullable();
            
            \$table->timestamps();

            \$table->unique('company_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('compliance_settings');
    }
};