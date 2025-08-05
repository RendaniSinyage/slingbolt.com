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
            // External platform integration fields
            $table->string('external_platform')->nullable()->after('created_by')->comment('External platform name (e.g., foodyman)');
            $table->string('external_id')->nullable()->after('external_platform')->comment('User ID in external platform');
            $table->timestamp('external_linked_at')->nullable()->after('external_id')->comment('When external platform was linked');
            
            // Add indexes for better performance on external lookups
            $table->index(['external_platform', 'external_id'], 'external_platform_user_idx');
            $table->index(['email', 'type'], 'email_type_idx');
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
            $table->dropIndex('external_platform_user_idx');
            $table->dropIndex('email_type_idx');
            $table->dropColumn(['external_platform', 'external_id', 'external_linked_at']);
        });
    }
};