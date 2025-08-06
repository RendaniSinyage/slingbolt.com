<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExternalPlatformFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('external_platform')->nullable()->after('created_by');
            $table->string('external_id')->nullable()->after('external_platform');
            $table->timestamp('external_linked_at')->nullable()->after('external_id');
            
            $table->index(['external_platform', 'external_id']);
            $table->index(['email', 'type']);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['external_platform', 'external_id']);
            $table->dropIndex(['email', 'type']);
            $table->dropColumn(['external_platform', 'external_id', 'external_linked_at']);
        });
    }
}