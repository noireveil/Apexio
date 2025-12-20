<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;
use App\Models\Project;
use App\Models\Task;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;

/**
 * Service Provider utama aplikasi.
 * 
 * Menangani registrasi policies, blade components, dan konfigurasi aplikasi.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services ke container.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services aplikasi.
     * 
     * Registrasi policies dan blade components.
     *
     * @return void
     */
    public function boot(): void
    {
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        
        Blade::component('layouts.app-with-sidebar', 'app-with-sidebar');
    }
}