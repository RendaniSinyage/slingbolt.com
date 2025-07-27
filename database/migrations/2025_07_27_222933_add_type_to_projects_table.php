// database/migrations/xxxx_add_type_to_projects_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToProjectsTable extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('type', 50)->default('standard')->after('tags');
        });
        
        // Set all existing projects to 'standard'
        DB::table('projects')->update(['type' => 'standard']);
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}