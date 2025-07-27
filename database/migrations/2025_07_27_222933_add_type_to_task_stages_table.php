// database/migrations/xxxx_add_type_to_task_stages_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToTaskStagesTable extends Migration
{
    public function up()
    {
        Schema::table('task_stages', function (Blueprint $table) {
            $table->string('type', 50)->default('standard')->after('project_id');
        });
        
        // Set all existing task stages to 'standard'
        DB::table('task_stages')->update(['type' => 'standard']);
    }

    public function down()
    {
        Schema::table('task_stages', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}