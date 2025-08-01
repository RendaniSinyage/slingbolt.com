<?php
// database/migrations/add_is_enabled_to_email_templates.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsEnabledToEmailTemplates extends Migration
{
    public function up()
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->boolean('is_enabled')->nullable()->after('from');  // NO DEFAULT
        });
    }

    public function down()
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn('is_enabled');
        });
    }
}