<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ManageProjects extends Component
{
    public $name;
    public $description;
    public $is_active = true;

    protected $listeners = ['project-created' => '$refresh', 'project-deleted' => '$refresh'];

    public function render()
    {
        return view('livewire.manage-projects', [
            'projects' => Auth::user()->projects()->latest()->get()
        ]);
    }

    public function deleteProject($projectId)
    {
        $project = Project::findOrFail($projectId);

        $this->authorize('delete', $project);

        $project->delete();
        
        $this->dispatch('project-deleted'); 
    }

    private function loadProjects(): void
    {
        $this->projects = Auth::user()->projects()->latest()->get();
    }
}