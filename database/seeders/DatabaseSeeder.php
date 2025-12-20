<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Comment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Comment::truncate();
        Task::truncate();
        DB::table('project_members')->truncate();
        Project::truncate();
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('🏗️  Membangun Mega Struktur Data Apexio...');

        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@apexio.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $figuranUsers = User::factory(50)->create([
            'password' => Hash::make('password'),
        ]);

        $allUsers = $figuranUsers->merge([$admin]);

        $this->command->info('👥 50 User Figuran siap bekerja.');

        $projectsScenario = [
            [
                'name' => 'E-Commerce Platform Revamp',
                'desc' => 'Redesigning the main marketplace with Laravel 11. High priority on performance.',
                'tasks' => ['Fix Payment Gateway', 'Optimize Database Queries', 'Frontend API Integration', 'Setup Redis Cache']
            ],
            [
                'name' => 'Q4 Marketing Campaign',
                'desc' => 'End of year sales strategy and social media content planning.',
                'tasks' => ['Design Instagram Ads', 'Budget Approval', 'Influencer Outreach', 'Draft Copywriting']
            ],
            [
                'name' => 'Internal HR System',
                'desc' => 'Employee attendance and payroll management system update.',
                'tasks' => ['Payroll Calculation Logic', 'Employee Dashboard', 'Export to PDF Feature', 'Server Migration']
            ],
        ];

        $techChatLog = [
            "I've pushed the latest changes to staging.",
            "Can you check the logs? I'm getting a 500 error.",
            "Design looks good, but font size is too small.",
            "Approved! Please merge to main.",
            "We need to discuss this in the daily standup.",
            "This is blocking the release.",
            "Fixed in commit #a1b2c3d.",
            "Client requested a change here.",
            "Documentation is updated.",
            "Wait, I think we missed an edge case."
        ];

        foreach ($projectsScenario as $scenario) {
            $project = Project::create([
                'owner_id' => $admin->id,
                'name' => $scenario['name'],
                'description' => $scenario['desc'],
            ]);

            $members = $figuranUsers->random(15);
            
            $project->members()->attach($admin->id, ['role' => 'Admin']);
            
            foreach ($members as $member) {
                $project->members()->attach($member->id, ['role' => (rand(1, 10) > 8) ? 'Admin' : 'Member']);
            }

            // Generate Task
            $this->createDramaTasks($project, $members, $scenario['tasks'], $techChatLog, 3, 'critical', 'late');
            $this->createDramaTasks($project, $members, $scenario['tasks'], $techChatLog, 4, 'high', 'soon');
            $this->createDramaTasks($project, $members, $scenario['tasks'], $techChatLog, 6, 'medium', 'future');
        }

        $adjectives = ['Agile', 'Global', 'NextGen', 'Cloud', 'Secure', 'Smart', 'Alpha', 'Omega', 'Prime', 'Dynamic'];
        $nouns = ['Solutions', 'Logistics', 'Finance', 'Healthcare', 'Education', 'Network', 'Analytics', 'AI', 'Robotics', 'DevOps'];
        
        $fillerChatLog = [
            "Any update on this?", "Looking into it.", "Almost done.", "Can someone review?", 
            "Great job!", "Lets postpone this.", "Meeting at 2 PM.", "Requires more info.", 
            "Checking...", "Done."
        ];

        $this->command->info('🏗️  Sedang membangun 47 Proyek tambahan & ratusan tugas...');
        
        $bar = $this->command->getOutput()->createProgressBar(47);
        $bar->start();

        for ($i = 1; $i <= 47; $i++) {
            $randomOwner = $allUsers->random();
            $projectName = $faker->randomElement($adjectives) . ' ' . $faker->randomElement($nouns) . ' Project ' . $faker->numerify('v#.#');
            
            $project = Project::create([
                'owner_id' => $randomOwner->id,
                'name' => $projectName,
                'description' => $faker->catchPhrase,
            ]);

            $members = $figuranUsers->random(rand(5, 10));
            
            if (!$members->contains($randomOwner)) {
                $project->members()->attach($randomOwner->id, ['role' => 'Admin']);
            }
            
            foreach ($members as $member) {
                if ($member->id !== $randomOwner->id) {
                    $project->members()->attach($member->id, ['role' => 'Member']);
                }
            }

            for ($t = 0; $t < 12; $t++) {
                $status = $faker->randomElement(['To-Do', 'In-Progress', 'Done']);
                $priority = $faker->randomElement(['low', 'medium', 'high', 'critical']);
                $dueDate = (rand(1, 10) <= 3) ? now()->subDays(rand(1, 20)) : now()->addDays(rand(1, 30));

                $task = Task::create([
                    'project_id' => $project->id,
                    'assignee_id' => $members->random()->id,
                    'title' => $faker->bs,
                    'description' => $faker->paragraph,
                    'priority' => $priority,
                    'status' => $status,
                    'due_date' => $dueDate,
                    'created_at' => now()->subDays(rand(1, 60)),
                ]);

                $commentCount = rand(10, 15);
                $commentsData = [];
                for ($c = 0; $c < $commentCount; $c++) {
                    $commentsData[] = [
                        'task_id' => $task->id,
                        'user_id' => $members->random()->id,
                        'body' => $faker->randomElement($fillerChatLog) . " " . $faker->sentence,
                        'created_at' => now()->subHours(rand(1, 500)),
                        'updated_at' => now(),
                    ];
                }
                Comment::insert($commentsData);
            }
            $bar->advance();
        }
        $bar->finish();
        $this->command->info('');

        $this->command->info('🚀 POPULASI DATA SELESAI!');
        $this->command->info("📊 Total Data: 50 Users | 50 Projects | ~600 Tasks | ~8000 Comments");
        $this->command->info('---------------------------------------------------------------------');
        $this->command->info('👉 [PANDUAN DEMO / PENGUJIAN]:');
        $this->command->info('   1. DAFTAR (REGISTER): Gunakan email PRIBADI Anda sendiri untuk mencoba fitur ini.');
        $this->command->info('   2. LUPA PASSWORD: Agar email reset terkirim, Anda WAJIB mengatur');
        $this->command->info('      Sandi Aplikasi Gmail (SMTP) di file .env masing-masing.');
        $this->command->info('   3. LOGIN ADMIN (Untuk melihat data dummy):');
        $this->command->info('      Email: admin@apexio.com | Password: password');
        $this->command->info('---------------------------------------------------------------------');
    }

    private function createDramaTasks($project, $members, $taskTitles, $chatLogs, $count, $priority, $timeFrame)
    {
        for ($i = 0; $i < $count; $i++) {
            $dueDate = match ($timeFrame) {
                'late' => now()->subDays(rand(1, 5)),
                'soon' => now()->addHours(rand(1, 20)),
                'future' => now()->addDays(rand(3, 14)),
            };

            $status = match ($timeFrame) {
                'late' => ['To-Do', 'In-Progress'][rand(0, 1)],
                default => ['To-Do', 'In-Progress', 'Done'][rand(0, 2)],
            };

            $title = $taskTitles[array_rand($taskTitles)] . ' - Module ' . rand(1, 9);
            
            $task = Task::create([
                'project_id' => $project->id,
                'assignee_id' => $members->random()->id,
                'title' => $title,
                'description' => "Detailed specifications for this task. Please ensure high code quality standards. Reference ID: " . strtoupper(uniqid()),
                'priority' => $priority,
                'status' => $status,
                'due_date' => $dueDate,
                'created_at' => now()->subDays(rand(5, 15)),
            ]);

            $numComments = rand(10, 15);
            $commentsData = [];
            for ($k = 0; $k < $numComments; $k++) {
                $commentsData[] = [
                    'task_id' => $task->id,
                    'user_id' => $members->random()->id,
                    'body' => $chatLogs[array_rand($chatLogs)],
                    'created_at' => now()->subHours(rand(1, 100)),
                    'updated_at' => now(),
                ];
            }
            Comment::insert($commentsData);
        }
    }
}