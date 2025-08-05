<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlanTrackingColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('previous_plan')->nullable()->after('plan');
            $table->boolean('has_used_trial')->default(false)->after('trial_expire_date');
            
            // Add index for better performance when querying downgraded users
            $table->index(['plan', 'previous_plan']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['plan', 'previous_plan']);
            $table->dropColumn(['previous_plan', 'has_used_trial']);
        });
    }
}