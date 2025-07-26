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
        Schema::table('users', function (Blueprint $table) {
            $table->string('registration_ip', 45)->nullable()->after('last_login_at')->comment('IP address used during registration');
            $table->string('last_login_ip', 45)->nullable()->after('registration_ip')->comment('IP address of last login');
            $table->text('user_agent')->nullable()->after('last_login_ip')->comment('Browser/device information during registration');
            
            // Add indexes for performance when querying by IP
            $table->index('registration_ip', 'users_registration_ip_index');
            $table->index('last_login_ip', 'users_last_login_ip_index');
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
            // Drop indexes first
            $table->dropIndex('users_registration_ip_index');
            $table->dropIndex('users_last_login_ip_index');
            
            // Drop columns
            $table->dropColumn(['registration_ip', 'last_login_ip', 'user_agent']);
        });
    }
};