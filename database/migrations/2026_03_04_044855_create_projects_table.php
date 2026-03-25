<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_id')->constrained('users');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#6366f1');
            $table->string('icon')->default('folder');
            $table->enum('status', ['planning','active','on_hold','completed','archived'])->default('planning');
            $table->enum('type', ['scrum','kanban','waterfall'])->default('scrum');
            $table->enum('visibility', ['private','team','public'])->default('team');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->string('currency', 3)->default('VND');
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['workspace_id', 'slug']);
        });

        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['manager','developer','designer','tester','viewer'])->default('developer');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
            $table->unique(['project_id', 'user_id']);
        });

        Schema::create('project_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('structure'); // columns, labels, custom fields...
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
    }
};
