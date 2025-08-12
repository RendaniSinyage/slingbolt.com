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
        Schema::table('compliance_settings', function (Blueprint $table) {
            $table->dropUnique('compliance_settings_company_id_unique');
            $table->dropConstrainedForeignId('company_id');
            $table->integer('created_by')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('compliance_settings', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->unique('company_id');
        });
    }
};
