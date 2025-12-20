<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Komponen Livewire untuk menampilkan detail project.
 * 
 * Menampilkan informasi lengkap tentang satu project.
 */
class ProjectDetail extends Component
{
    /**
     * Instance project yang ditampilkan.
     *
     * @var Project
     */
    public Project $project;

    /**
     * Inisialisasi komponen dengan project.
     *
     * @param Project $project Project yang akan ditampilkan
     * @return void
     */
    public function mount(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Render komponen detail project.
     *
     * @return \Illuminate\View\View View project detail
     */
    #[Layout('layouts.app-with-sidebar')] 
    public function render()
    {
        return view('livewire.project-detail');
    }
}