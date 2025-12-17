<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@apexio.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        // 2. Create Regular User (Demo Account)
        $user = User::firstOrCreate(
            ['email' => 'user@apexio.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );

        // 3. Create a Demo Project for Regular User
        $project = Project::firstOrCreate(
            [
                'name' => 'Website Redesign',
                'owner_id' => $user->id
            ],
            [
                'description' => 'Overhaul the corporate website with a modern design using Laravel & Livewire.',
            ]
        );

        // Pastikan owner otomatis jadi member admin di projectnya sendiri
        if (!$project->members()->where('user_id', $user->id)->exists()) {
            $project->members()->attach($user->id, ['role' => 'Admin']);
        }

        // 4. Create Dummy Tasks for the Project
        // Hapus task lama jika ada agar tidak menumpuk saat re-seed
        Task::where('project_id', $project->id)->delete();

        $tasks = [
            [
                'title' => 'Setup Laravel Environment', // FIX: 'name' diubah jadi 'title'
                'description' => 'Install Laravel 11, configure .env, and setup database connection.',
                'status' => 'done', // Kanban: Done
                'priority' => 'high',
                'due_date' => now()->subDays(2), // Late
                // 'position' => 1, // DIHAPUS: Karena kolom position tidak ada di tabel tasks
            ],
            [
                'title' => 'Design Homepage Mockup',
                'description' => 'Create a high-fidelity mockup using Figma/Adobe XD.',
                'status' => 'in_progress', // Kanban: In Progress
                'priority' => 'high',
                'due_date' => now()->addDays(2), // Soon
                // 'position' => 1,
            ],
            [
                'title' => 'Develop Authentication',
                'description' => 'Implement login, register, and password reset functionality.',
                'status' => 'in_progress',
                'priority' => 'medium',
                'due_date' => now()->addDays(5),
                // 'position' => 2,
            ],
            [
                'title' => 'User Testing',
                'description' => 'Conduct usability testing with internal team.',
                'status' => 'todo', // Kanban: To Do
                'priority' => 'low',
                'due_date' => now()->addWeek(),
                // 'position' => 1,
            ],
            [
                'title' => 'Deploy to Production',
                'description' => 'Setup VPS and deploy the application.',
                'status' => 'todo',
                'priority' => 'high',
                'due_date' => now()->addWeeks(2),
                // 'position' => 2,
            ],
        ];

        foreach ($tasks as $taskData) {
            Task::create(array_merge($taskData, [
                'project_id' => $project->id,
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        // 5. Create Extra Random Users (for Member Invite testing)
        User::factory(5)->create();

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin: admin@apexio.com | Pass: password');
        $this->command->info('User:  user@apexio.com  | Pass: password');
    }
}