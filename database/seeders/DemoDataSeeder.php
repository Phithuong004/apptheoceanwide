<?php
namespace Database\Seeders;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\TaskLabel;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name'     => 'Admin',
                'password' => bcrypt('phithuong1211'),
                'status'   => 'active',
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('super_admin');

        // Create demo workspace
        $workspace = Workspace::create([
            'name'     => 'ProjectHub Demo',
            'slug'     => 'projecthub-demo',
            'owner_id' => $admin->id,
        ]);
        $workspace->members()->attach($admin->id, ['role' => 'owner', 'joined_at' => now()]);

        // Create team members
        $members = User::factory(5)->create();
        foreach ($members as $member) {
            $member->assignRole('member');
            $workspace->members()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);
        }

        // Create client
        $client = Client::create([
            'workspace_id' => $workspace->id,
            'name'         => 'ABC Corporation',
            'company'      => 'ABC Corp',
            'email'        => 'contact@abc.com',
            'status'       => 'active',
        ]);

        // Create project
        $project = Project::create([
            'workspace_id' => $workspace->id,
            'client_id'    => $client->id,
            'owner_id'     => $admin->id,
            'name'         => 'E-commerce Platform',
            'slug'         => 'ecommerce-platform',
            'type'         => 'scrum',
            'visibility'   => 'team',
            'status'       => 'active',
            'start_date'   => now()->subMonth(),
            'end_date'     => now()->addMonths(2),
        ]);

        foreach ($members->push($admin) as $member) {
            $project->members()->attach($member->id, [
                'role'      => $member->id === $admin->id ? 'manager' : 'developer',
                'joined_at' => now(),
            ]);
        }

        // Default labels
        $labels = collect([
            ['name' => 'Bug',      'color' => '#ef4444'],
            ['name' => 'Feature',  'color' => '#6366f1'],
            ['name' => 'Hotfix',   'color' => '#f59e0b'],
            ['name' => 'Refactor', 'color' => '#10b981'],
        ])->map(fn($l) => $project->labels()->create($l));

        // Create sprint
        $sprint = Sprint::create([
            'project_id' => $project->id,
            'name'       => 'Sprint 1',
            'goal'       => 'Hoàn thiện tính năng cơ bản',
            'status'     => 'active',
            'start_date' => now()->startOfWeek(),
            'end_date'   => now()->addWeeks(2)->endOfWeek(),
            'started_at' => now(),
        ]);

        // Create tasks
        $statuses  = ['backlog','todo','in_progress','in_review','done'];
        $priorities= ['low','medium','high','urgent'];
        $taskTitles = [
            'Thiết kế UI trang chủ', 'Xây dựng API xác thực',
            'Tích hợp thanh toán VNPay', 'Viết unit tests cho auth',
            'Tối ưu database queries', 'Deploy lên server staging',
            'Review code PR #12', 'Fix bug giỏ hàng mobile',
            'Cập nhật tài liệu API', 'Implement full-text search',
        ];

        foreach ($taskTitles as $i => $title) {
            $task = Task::create([
                'workspace_id' => $workspace->id,
                'project_id'   => $project->id,
                'sprint_id'    => $sprint->id,
                'reporter_id'  => $admin->id,
                'assignee_id'  => $members->random()->id,
                'title'        => $title,
                'status'       => $statuses[$i % count($statuses)],
                'priority'     => $priorities[$i % count($priorities)],
                'type'         => $i % 3 === 0 ? 'bug' : 'task',
                'story_points' => collect([1,2,3,5,8])->random(),
                'position'     => $i,
                'due_date'     => now()->addDays(rand(1, 14)),
            ]);

            // Attach random label
            $task->labels()->attach($labels->random()->id);
        }

        $this->command->info('✅ Demo data seeded successfully!');
        $this->command->info('   👤 Admin: admin@admin.com / phithuong1211');
    }
}
