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

    public function openCreateModal()
    {
        $this->reset(['name', 'description']);
        $this->dispatch('open-create-modal'); 
    }

    public function saveProject()
    {
        $this->validate([
            'name' => 'required|min:3|max:255',
            'description' => 'nullable|string'
        ]);

        $project = Project::create([
            'owner_id' => Auth::id(),
            'name' => $this->name,
            'description' => $this->description,
        ]);

        $project->members()->attach(Auth::id(), ['role' => 'Admin']);

        $this->reset(['name', 'description']);
        $this->dispatch('close-create-modal');
        
        $this->dispatch('project-created');
    }

    public function deleteProject($projectId)
    {
        $project = Project::findOrFail($projectId);

        $this->authorize('delete', $project);

        $project->delete();
        
        $this->dispatch('project-deleted'); 
    }
}