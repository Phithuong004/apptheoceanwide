<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_subtasks', function (Blueprint $table) {
            if (!Schema::hasColumn('task_subtasks', 'deadline')) {
                $table->date('deadline')->nullable()->after('status');
            }
            if (!Schema::hasColumn('task_subtasks', 'comments')) {
                $table->text('comments')->nullable()->after('deadline');
            }
        });
    }
    
    public function down(): void
    {
        Schema::table('task_subtasks', function (Blueprint $table) {
            if (Schema::hasColumn('task_subtasks', 'deadline')) {
                $table->dropColumn('deadline');
            }
            if (Schema::hasColumn('task_subtasks', 'comments')) {
                $table->dropColumn('comments');
            }
        });
    }
    
};
